import './bootstrap';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

function looksLikeHtml(value) {
    return /<\/?[a-z][\s\S]*>/i.test(value || '');
}

function stripHtml(value) {
    return String(value || '').replace(/<[^>]*>/g, '').trim();
}

const quillToolbarOptions = [
    [{ header: [1, 2, 3, false] }],
    ['bold', 'italic', 'underline'],
    [{ list: 'ordered' }, { list: 'bullet' }],
    ['blockquote', 'link'],
    ['clean'],
];

const quillToolbarLabels = {
    bold: 'Vet',
    italic: 'Cursief',
    underline: 'Onderstrepen',
    blockquote: 'Citaat',
    link: 'Link toevoegen',
    clean: 'Opmaak wissen',
};

function isSafeRichTextUrl(value) {
    const url = String(value || '').trim();

    return !url.startsWith('//') && /^(https?:|mailto:|tel:|\/|#)/i.test(url);
}

function normalizeEditorHtml(html) {
    const template = document.createElement('template');
    template.innerHTML = html;

    template.content.querySelectorAll('script, style, iframe, object, embed, form, input, button, textarea, select').forEach((node) => {
        node.remove();
    });

    template.content.querySelectorAll('*').forEach((node) => {
        Array.from(node.attributes).forEach((attribute) => {
            const name = attribute.name.toLowerCase();
            const tagName = node.tagName.toLowerCase();

            if (tagName === 'a' && ['href', 'target', 'rel'].includes(name)) {
                return;
            }

            node.removeAttribute(attribute.name);
        });
    });

    template.content.querySelectorAll('a').forEach((link) => {
        const href = link.getAttribute('href') || '';

        if (!isSafeRichTextUrl(href)) {
            link.removeAttribute('href');
            link.removeAttribute('target');
            link.removeAttribute('rel');
            return;
        }

        if (/^https?:\/\//i.test(href)) {
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener noreferrer');
        }
    });

    const walker = document.createTreeWalker(template.content, NodeFilter.SHOW_TEXT);
    while (walker.nextNode()) {
        walker.currentNode.nodeValue = walker.currentNode.nodeValue.replace(/[\u00a0\u202f\u2007]+/g, ' ');
    }

    return template.innerHTML.trim();
}

function quillHtml(quill) {
    const html = typeof quill.getSemanticHTML === 'function'
        ? quill.getSemanticHTML()
        : quill.root.innerHTML;

    return normalizeEditorHtml(html);
}

function applyQuillToolbarLabels(toolbar) {
    if (!toolbar) {
        return;
    }

    Object.entries(quillToolbarLabels).forEach(([format, label]) => {
        toolbar.querySelectorAll(`.ql-${format}`).forEach((control) => {
            control.setAttribute('title', label);
            control.setAttribute('aria-label', label);
        });
    });

    toolbar.querySelectorAll('.ql-list[value="ordered"]').forEach((control) => {
        control.setAttribute('title', 'Genummerde lijst');
        control.setAttribute('aria-label', 'Genummerde lijst');
    });

    toolbar.querySelectorAll('.ql-list[value="bullet"]').forEach((control) => {
        control.setAttribute('title', 'Opsomming');
        control.setAttribute('aria-label', 'Opsomming');
    });

    toolbar.querySelectorAll('.ql-header').forEach((control) => {
        control.setAttribute('title', 'Tekststijl');
        control.setAttribute('aria-label', 'Tekststijl');
    });
}

document.querySelectorAll('[data-theme-primary-color-default]').forEach((input) => {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--color-primary').trim();

    if (/^#[0-9a-f]{6}$/i.test(primaryColor)) {
        input.value = primaryColor.toUpperCase();
    }
});

document.querySelectorAll('[data-mobile-nav-toggle]').forEach((toggle) => {
    const sidebar = toggle.closest('.dashboard-sidebar');

    if (!sidebar) {
        return;
    }

    toggle.addEventListener('click', () => {
        const isOpen = sidebar.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
});

document.querySelectorAll('[data-kvk-form]').forEach((kvkForm) => {
    const kvkInput = kvkForm.querySelector('[data-kvk-number]');
    const companyNameInput = kvkForm.querySelector('[data-company-name]');
    const companyOptions = kvkForm.querySelector('[data-company-options]');
    const lookupButton = kvkForm.querySelector('[data-kvk-lookup]');
    const feedback = kvkForm.querySelector('[data-kvk-feedback]');
    let searchTimeout = null;

    const setFeedback = (message, type = '') => {
        if (!feedback) {
            return;
        }

        feedback.textContent = message;
        feedback.dataset.state = type;
    };

    const fillFields = (data) => {
        Object.entries(data).forEach(([key, value]) => {
            const field = kvkForm.querySelector(`[data-kvk-target="${key}"]`);

            if (!field || value === null || value === undefined) {
                return;
            }

            field.value = value;
        });
    };

    const fillSuggestions = (results) => {
        if (!companyOptions) {
            return;
        }

        companyOptions.innerHTML = '';

        results.forEach((result) => {
            const option = document.createElement('option');
            option.value = result.company_name;
            option.label = [result.kvk_number, result.city].filter(Boolean).join(' - ');
            companyOptions.appendChild(option);
        });
    };

    companyNameInput?.addEventListener('input', () => {
        const companyName = companyNameInput.value.trim();
        const searchUrl = companyNameInput.dataset.kvkSearchUrl;

        if (searchTimeout) {
            window.clearTimeout(searchTimeout);
        }

        if (!searchUrl || companyName.length < 2) {
            fillSuggestions([]);
            return;
        }

        searchTimeout = window.setTimeout(async () => {
            try {
                const response = await window.axios.post(searchUrl, {
                    company_name: companyName,
                });

                fillSuggestions(response.data.data ?? []);
            } catch {
                fillSuggestions([]);
            }
        }, 250);
    });

    lookupButton?.addEventListener('click', async () => {
        const kvkNumber = kvkInput?.value.replace(/\D/g, '') ?? '';
        const companyName = companyNameInput?.value.trim() ?? '';
        const lookupUrl = lookupButton.dataset.kvkUrl;

        if ((!companyName && kvkNumber.length !== 8) || !lookupUrl) {
            setFeedback('Vul een bedrijfsnaam of een geldig KVK-nummer in.', 'error');
            return;
        }

        lookupButton.disabled = true;
        setFeedback('Bedrijfsgegevens worden opgehaald...', 'loading');

        try {
            const response = await window.axios.post(lookupUrl, {
                kvk_number: kvkNumber || null,
                company_name: companyName || null,
            });

            fillFields(response.data.data ?? {});
            setFeedback('Bedrijfsgegevens zijn ingevuld. Controleer het resultaat voor je verdergaat.', 'success');
        } catch (error) {
            const message = error.response?.data?.message
                ?? 'Het ophalen van KVK-gegevens is niet gelukt.';

            setFeedback(message, 'error');
        } finally {
            lookupButton.disabled = false;
        }
    });

});

document.querySelectorAll('[data-step-form]').forEach((stepForm) => {
    const stepPanels = Array.from(stepForm.querySelectorAll('[data-step-panel]'));
    const stepIndicators = Array.from(stepForm.querySelectorAll('[data-step-indicator]'));
    let currentStep = Number(stepForm.dataset.initialStep ?? 1);

    const syncSteps = () => {
        stepPanels.forEach((panel) => {
            const isActive = Number(panel.dataset.stepPanel) === currentStep;
            panel.classList.toggle('is-active', isActive);
        });

        stepIndicators.forEach((indicator) => {
            const step = Number(indicator.dataset.stepIndicator);
            indicator.classList.toggle('is-active', step === currentStep);
            indicator.classList.toggle('is-complete', step < currentStep);
        });
    };

    const validateCurrentStep = () => {
        const currentPanel = stepForm.querySelector(`[data-step-panel="${currentStep}"]`);

        if (!currentPanel) {
            return true;
        }

        const fields = Array.from(currentPanel.querySelectorAll('input, select, textarea'));

        for (const field of fields) {
            if (typeof field.reportValidity === 'function' && !field.reportValidity()) {
                field.focus();
                return false;
            }
        }

        return true;
    };

    stepForm.querySelectorAll('[data-step-next]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!validateCurrentStep()) {
                return;
            }

            currentStep = Math.min(currentStep + 1, stepPanels.length);
            syncSteps();
        });
    });

    stepForm.querySelectorAll('[data-step-prev]').forEach((button) => {
        button.addEventListener('click', () => {
            currentStep = Math.max(currentStep - 1, 1);
            syncSteps();
        });
    });

    syncSteps();
});

document.querySelectorAll('[data-confirmation-builder]').forEach((builder) => {
    const panels = Array.from(builder.querySelectorAll('[data-builder-panel]'));
    const openButtons = Array.from(builder.querySelectorAll('[data-builder-open]'));
    const closeButtons = Array.from(builder.querySelectorAll('[data-builder-close]'));

    const previewTarget = (name) => builder.querySelector(`[data-preview-target="${name}"]`);

    const fallbackText = (value, fallback) => {
        const text = `${value ?? ''}`.trim();

        return text === '' ? fallback : text;
    };

    const setPreviewText = (name, value, fallback = 'Nog niet ingevuld') => {
        const target = previewTarget(name);

        if (!target) {
            return;
        }

        target.textContent = fallbackText(value, fallback);
    };

    const cleanRichText = (html) => {
        return normalizeEditorHtml(html);
    };

    const setPreviewHtml = (name, value, fallbackHtml) => {
        const target = previewTarget(name);

        if (!target) {
            return;
        }

        const html = cleanRichText(value ?? '');
        target.innerHTML = html === '' ? fallbackHtml : html;
    };

    const openPanel = (panelName, shouldFocus = true) => {
        panels.forEach((panel) => {
            const isOpen = panel.dataset.builderPanel === panelName;
            panel.hidden = !isOpen;
        });

        openButtons.forEach((button) => {
            button.setAttribute('aria-expanded', button.dataset.builderOpen === panelName ? 'true' : 'false');
        });

        if (!shouldFocus) {
            return;
        }

        const activePanel = panels.find((panel) => panel.dataset.builderPanel === panelName);
        const firstField = activePanel?.querySelector('input:not([type="hidden"]):not([readonly]), select, textarea');
        firstField?.focus({ preventScroll: true });
        activePanel?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    };

    const closePanels = () => {
        panels.forEach((panel) => {
            panel.hidden = true;
        });

        openButtons.forEach((button) => {
            button.setAttribute('aria-expanded', 'false');
        });
    };

    const updateContactPreview = (option) => {
        if (!option) {
            return;
        }

        const person = option.dataset.contactPerson ?? '';
        const email = option.dataset.contactEmail ?? '';
        const address = (option.dataset.contactAddress ?? '').split('||').filter(Boolean).join(' · ');

        setPreviewText('client-company', option.dataset.contactCompany || option.dataset.contactLabel, 'Nog te selecteren');
        setPreviewText('client-person', person, 'Contactpersoon');
        setPreviewText('client-email', email, 'E-mailadres opdrachtgever');
        setPreviewText('client-address', address, 'Adres opdrachtgever');
        setPreviewText('aside-client', option.dataset.contactCompany || option.dataset.contactLabel, 'Nog niet geselecteerd');

        const personField = builder.querySelector('[data-selected-contact-person]');
        const emailField = builder.querySelector('[data-selected-contact-email]');

        if (personField) {
            personField.value = person;
        }

        if (emailField) {
            emailField.value = email;
        }
    };

    const updatePreviewFromField = (field) => {
        const previewName = field.dataset.previewInput;

        if (!previewName) {
            return;
        }

        if (previewName === 'description') {
            setPreviewHtml('description', field.value, '<p>Beschrijf hier de opdracht, werkzaamheden, verwachtingen en oplevering.</p>');
            return;
        }

        if (previewName === 'title') {
            const titleTarget = previewTarget('title');
            const hasTitle = `${field.value ?? ''}`.trim() !== '';

            setPreviewText('title', field.value, 'Bijvoorbeeld: Website development');
            titleTarget?.classList.toggle('is-placeholder', !hasTitle);
            return;
        }

        setPreviewText(previewName, field.value);
    };

    builder.addEventListener('input', (event) => {
        if (!(event.target instanceof HTMLElement)) {
            return;
        }

        updatePreviewFromField(event.target);
    });

    builder.addEventListener('change', (event) => {
        if (!(event.target instanceof HTMLElement)) {
            return;
        }

        updatePreviewFromField(event.target);
    });

    builder.addEventListener('contact-search:selected', (event) => {
        updateContactPreview(event.detail?.option);
    });

    openButtons.forEach((button) => {
        button.addEventListener('click', () => {
            openPanel(button.dataset.builderOpen);
        });

        button.addEventListener('keydown', (event) => {
            if (button.tagName === 'BUTTON' || !['Enter', ' '].includes(event.key)) {
                return;
            }

            event.preventDefault();
            openPanel(button.dataset.builderOpen);
        });
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', closePanels);
    });

    builder.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closePanels();
        }
    });

    builder.addEventListener('submit', (event) => {
        const contactValue = builder.querySelector('[data-contact-search-value]');
        const contactInput = builder.querySelector('[data-contact-search-input]');
        const titleInput = builder.querySelector('#title');
        const descriptionInput = builder.querySelector('[data-quill-input]');
        const descriptionWrapper = builder.querySelector('[data-quill-field]');

        if (contactValue && contactInput && contactValue.value.trim() === '') {
            event.preventDefault();
            openPanel('client');
            contactInput.setCustomValidity('Kies een opdrachtgever uit de lijst.');
            contactInput.reportValidity();
            contactInput.addEventListener('input', () => contactInput.setCustomValidity(''), { once: true });
            return;
        }

        if (titleInput && titleInput.value.trim() === '') {
            event.preventDefault();
            openPanel('confirmation');
            titleInput.setCustomValidity('Vul een titel in.');
            titleInput.reportValidity();
            titleInput.addEventListener('input', () => titleInput.setCustomValidity(''), { once: true });
            return;
        }

        if (descriptionInput && descriptionInput.value.trim() === '') {
            event.preventDefault();
            openPanel('confirmation');
            descriptionWrapper?.__quill?.focus();
        }
    });

    builder.querySelectorAll('[data-preview-input]').forEach((field) => updatePreviewFromField(field));

    const initialContact = builder.querySelector(`[data-contact-search-option][data-contact-id="${builder.querySelector('[data-contact-search-value]')?.value}"]`);
    updateContactPreview(initialContact);

    if (builder.dataset.initialPanel) {
        openPanel(builder.dataset.initialPanel, false);
    }
});

document.querySelectorAll('[data-contact-search]').forEach((contactSearch) => {
    const input = contactSearch.querySelector('[data-contact-search-input]');
    const valueInput = contactSearch.querySelector('[data-contact-search-value]');
    const results = contactSearch.querySelector('[data-contact-search-results]');
    const emptyState = contactSearch.querySelector('[data-contact-search-empty]');
    const optionElements = Array.from(contactSearch.querySelectorAll('[data-contact-search-option]'));
    const form = contactSearch.closest('form');
    const maxResults = 8;

    if (!input || !valueInput || !results) {
        return;
    }

    let visibleOptions = [];
    let activeIndex = -1;

    const normalise = (value) => value.toLocaleLowerCase('nl-NL');

    const openList = () => {
        results.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    };

    const closeList = () => {
        results.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        activeIndex = -1;

        optionElements.forEach((option) => {
            option.classList.remove('is-active');
        });
    };

    const setActiveOption = (index) => {
        optionElements.forEach((option) => {
            option.classList.remove('is-active');
        });

        activeIndex = index;

        const option = visibleOptions[activeIndex];

        if (!option) {
            input.removeAttribute('aria-activedescendant');
            return;
        }

        option.classList.add('is-active');
        input.setAttribute('aria-activedescendant', option.id);
        option.scrollIntoView({ block: 'nearest' });
    };

    const markSelectedOption = () => {
        optionElements.forEach((option) => {
            const isSelected = option.dataset.contactId === valueInput.value;

            option.classList.toggle('is-selected', isSelected);
            option.setAttribute('aria-selected', isSelected ? 'true' : 'false');
        });
    };

    const selectOption = (option) => {
        valueInput.value = option.dataset.contactId ?? '';
        input.value = option.dataset.contactLabel ?? '';
        input.setCustomValidity('');
        markSelectedOption();
        contactSearch.dispatchEvent(new CustomEvent('contact-search:selected', {
            bubbles: true,
            detail: {
                id: valueInput.value,
                label: input.value,
                option,
            },
        }));
        closeList();
    };

    const filterOptions = () => {
        const query = input.value.trim();

        optionElements.forEach((option) => {
            option.hidden = true;
            option.classList.remove('is-active');
        });

        if (query.length < 1) {
            if (emptyState) {
                emptyState.hidden = true;
            }

            closeList();
            return;
        }

        const normalisedQuery = normalise(query);
        visibleOptions = optionElements
            .filter((option) => normalise(option.dataset.contactSearchText ?? '').includes(normalisedQuery))
            .slice(0, maxResults);

        if (emptyState) {
            emptyState.hidden = visibleOptions.length > 0;
        }

        visibleOptions.forEach((option) => {
            option.hidden = false;
        });

        openList();
        setActiveOption(visibleOptions.length > 0 ? 0 : -1);
    };

    input.addEventListener('input', () => {
        const selectedOption = optionElements.find((option) => option.dataset.contactId === valueInput.value);

        if (!selectedOption || input.value !== selectedOption.dataset.contactLabel) {
            valueInput.value = '';
            markSelectedOption();
        }

        input.setCustomValidity('');
        filterOptions();
    });

    input.addEventListener('focus', () => {
        filterOptions();
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeList();
            return;
        }

        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();

            if (results.hidden) {
                filterOptions();
            }

            if (visibleOptions.length === 0) {
                return;
            }

            const direction = event.key === 'ArrowDown' ? 1 : -1;
            const nextIndex = (activeIndex + direction + visibleOptions.length) % visibleOptions.length;
            setActiveOption(nextIndex);
        }

        if (event.key === 'Enter' && !results.hidden && visibleOptions[activeIndex]) {
            event.preventDefault();
            selectOption(visibleOptions[activeIndex]);
        }
    });

    input.addEventListener('blur', () => {
        window.setTimeout(closeList, 120);
    });

    input.addEventListener('invalid', () => {
        if (!valueInput.value) {
            input.setCustomValidity('Kies een opdrachtgever uit de lijst.');
        }
    });

    optionElements.forEach((option) => {
        option.addEventListener('mousedown', (event) => {
            event.preventDefault();
        });

        option.addEventListener('click', () => {
            selectOption(option);
            input.focus();
        });
    });

    form?.addEventListener('submit', (event) => {
        if (valueInput.value) {
            input.setCustomValidity('');
            return;
        }

        input.setCustomValidity('Kies een opdrachtgever uit de lijst.');
        input.reportValidity();
        event.preventDefault();
    });

    const initialOption = optionElements.find((option) => option.dataset.contactId === valueInput.value);

    if (initialOption) {
        selectOption(initialOption);
    } else {
        markSelectedOption();
        closeList();
    }
});

document.querySelectorAll('[data-quill-field]').forEach((wrapper) => {
    const editorEl = wrapper.querySelector('[data-quill-editor]');
    const input = wrapper.querySelector('[data-quill-input]');
    const form = wrapper.closest('form');

    if (!editorEl || !input) {
        return;
    }

    const initialContent = input.value.trim();
    const isRequired = editorEl.dataset.quillRequired === 'true';

    const quill = new Quill(editorEl, {
        theme: 'snow',
        placeholder: editorEl.dataset.quillPlaceholder ?? '',
        bounds: wrapper,
        modules: {
            toolbar: quillToolbarOptions,
        },
    });
    wrapper.__quill = quill;
    applyQuillToolbarLabels(quill.getModule('toolbar')?.container);

    if (initialContent !== '') {
        if (looksLikeHtml(initialContent)) {
            quill.clipboard.dangerouslyPasteHTML(normalizeEditorHtml(initialContent));
        } else {
            quill.setText(initialContent);
        }
    }

    if (wrapper.dataset.aiAssist === 'true' && wrapper.dataset.aiAssistUrl) {
        const toolbar = quill.getModule('toolbar')?.container;

        if (toolbar) {
            const status = document.createElement('span');
            status.className = 'quill-ai-assist-status';

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'quill-ai-assist-button';
            button.textContent = 'AI-assist';

            button.addEventListener('click', async () => {
                if (quill.getText().trim() === '') {
                    status.textContent = 'Vul eerst een tekst in.';
                    return;
                }

                button.disabled = true;
                button.textContent = 'Bezig...';
                status.textContent = '';

                try {
                    const response = await fetch(wrapper.dataset.aiAssistUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        },
                        body: JSON.stringify({
                            text: quillHtml(quill),
                            context: wrapper.dataset.aiAssistContext ?? '',
                        }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message ?? 'Er ging iets mis.');
                    }

                    quill.setText('');
                    quill.clipboard.dangerouslyPasteHTML(normalizeEditorHtml(data.html));
                    syncInput();
                } catch (error) {
                    status.textContent = error.message ?? 'Er ging iets mis, probeer het opnieuw.';
                } finally {
                    button.disabled = false;
                    button.textContent = 'AI-assist';
                }
            });

            toolbar.append(button, status);
        }
    }

    let hasAttemptedSubmit = false;

    const syncInput = () => {
        const isEmpty = quill.getText().trim() === '';
        input.value = isEmpty ? '' : quillHtml(quill);
        const isInvalid = hasAttemptedSubmit && isRequired && isEmpty;
        quill.container.classList.toggle('is-invalid', isInvalid);
        wrapper.classList.toggle('is-invalid', isInvalid);
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('richtext:sync'));
    };
    wrapper.__syncQuillInput = syncInput;

    quill.on('text-change', syncInput);

    form?.addEventListener('submit', (event) => {
        syncInput();

        if (isRequired && quill.getText().trim() === '') {
            hasAttemptedSubmit = true;
            syncInput();
            event.preventDefault();
            quill.focus();
        }
    });

    syncInput();
});

document.querySelectorAll('[data-ai-toolkit]').forEach((panel) => {
    const formSelector = panel.dataset.aiFormSelector;
    const form = formSelector ? document.querySelector(formSelector) : null;
    const briefInput = panel.querySelector('[data-ai-brief]');
    const generateButton = panel.querySelector('[data-ai-generate]');
    const checkButton = panel.querySelector('[data-ai-check]');
    const status = panel.querySelector('[data-ai-status]');
    const results = panel.querySelector('[data-ai-check-results]');
    const descriptionWrapper = form?.querySelector('[data-ai-assist-context="opdrachtbeschrijving"]');
    const descriptionEditor = descriptionWrapper?.__quill;

    if (!form || !descriptionEditor) {
        return;
    }

    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    const setStatus = (message, state = '') => {
        if (!status) {
            return;
        }

        status.textContent = message;
        status.dataset.state = state;
    };

    const setBusy = (isBusy) => {
        if (generateButton) {
            generateButton.disabled = isBusy;
        }

        if (checkButton) {
            checkButton.disabled = isBusy;
        }
    };

    const postJson = async (url, payload) => {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify(payload),
        });
        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(data.message ?? 'Er ging iets mis.');
        }

        return data;
    };

    const fieldLabel = (field) => {
        if (field.id) {
            const matchingLabel = Array.from(form.querySelectorAll('label')).find((label) => label.htmlFor === field.id);

            if (matchingLabel) {
                return matchingLabel.textContent.trim();
            }
        }

        const localLabel = field.closest('div, section, details')?.querySelector('label');

        if (localLabel) {
            return localLabel.textContent.trim();
        }

        return (field.name || field.id || 'Veld').replace(/[_-]/g, ' ');
    };

    const fieldValue = (field) => {
        if (field.type === 'checkbox' || field.type === 'radio') {
            return field.checked ? (field.value || 'Ja') : '';
        }

        if (field.tagName === 'SELECT') {
            return Array.from(field.selectedOptions)
                .map((option) => option.textContent.trim())
                .filter(Boolean)
                .join(', ');
        }

        return field.value.trim();
    };

    const collectFormContext = () => {
        const lines = [];
        const fields = Array.from(form.querySelectorAll('input, select, textarea'));

        fields.forEach((field) => {
            if (
                field.disabled
                || field.matches('[type="hidden"], [type="file"], [type="password"], [type="submit"]')
                || field.matches('[data-ai-brief], [data-quill-input]')
                || ['_token', 'submit_action'].includes(field.name)
            ) {
                return;
            }

            const value = fieldValue(field);

            if (value === '') {
                return;
            }

            lines.push(`${fieldLabel(field)}: ${value}`);
        });

        return lines.join('\n');
    };

    const editorHtml = () => quillHtml(descriptionEditor);

    const replaceDescription = (html) => {
        descriptionEditor.setText('');
        descriptionEditor.clipboard.dangerouslyPasteHTML(normalizeEditorHtml(html));
        descriptionWrapper.__syncQuillInput?.();
    };

    const renderCheckResults = (data) => {
        if (!results) {
            return;
        }

        const score = document.createElement('p');
        score.className = 'ai-check-score';
        score.textContent = `Score: ${data.score ?? 0}/100`;

        const summary = document.createElement('p');
        summary.className = 'ai-check-summary';
        summary.textContent = data.summary ?? 'Controle afgerond.';

        const list = document.createElement('div');
        list.className = 'ai-check-list';

        const labels = {
            ok: 'Compleet',
            warning: 'Let op',
            missing: 'Mist',
        };

        (data.items ?? []).forEach((item) => {
            const itemEl = document.createElement('div');
            const itemStatus = ['ok', 'warning', 'missing'].includes(item.status) ? item.status : 'warning';
            itemEl.className = 'ai-check-item';
            itemEl.dataset.status = itemStatus;

            const heading = document.createElement('div');
            heading.className = 'ai-check-item-heading';

            const title = document.createElement('strong');
            title.textContent = item.label ?? 'Aandachtspunt';

            const badge = document.createElement('span');
            badge.className = 'ai-check-status';
            badge.textContent = labels[itemStatus] ?? 'Let op';

            const message = document.createElement('p');
            message.textContent = item.message ?? 'Controleer dit onderdeel.';

            heading.append(title, badge);
            itemEl.append(heading, message);
            list.appendChild(itemEl);
        });

        results.replaceChildren(score, summary, list);
        results.hidden = false;
    };

    generateButton?.addEventListener('click', async () => {
        const brief = briefInput?.value.trim() ?? '';

        if (brief === '') {
            setStatus('Beschrijf kort wat de opdracht inhoudt.', 'error');
            briefInput?.focus();
            return;
        }

        setBusy(true);
        setStatus('Concept wordt gemaakt...', 'loading');

        try {
            const data = await postJson(panel.dataset.aiGenerateUrl, {
                brief,
                form_context: collectFormContext(),
            });

            replaceDescription(data.html);
            setStatus('Concept is ingevuld. Loop de afspraken nog even zorgvuldig na.', 'success');
        } catch (error) {
            setStatus(error.message ?? 'Concept maken is niet gelukt.', 'error');
        } finally {
            setBusy(false);
        }
    });

    checkButton?.addEventListener('click', async () => {
        descriptionWrapper.__syncQuillInput?.();
        setBusy(true);
        setStatus('Opdrachtbevestiging wordt gecontroleerd...', 'loading');

        try {
            const data = await postJson(panel.dataset.aiCheckUrl, {
                text: editorHtml(),
                form_context: collectFormContext(),
            });

            renderCheckResults(data);
            setStatus('Controle afgerond.', 'success');
        } catch (error) {
            setStatus(error.message ?? 'Controle is niet gelukt.', 'error');
        } finally {
            setBusy(false);
        }
    });
});
