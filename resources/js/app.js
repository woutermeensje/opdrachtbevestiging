import './bootstrap';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

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
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
            ],
        },
    });

    if (initialContent !== '') {
        quill.clipboard.dangerouslyPasteHTML(initialContent);
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
                            text: quill.getSemanticHTML(),
                            context: wrapper.dataset.aiAssistContext ?? '',
                        }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message ?? 'Er ging iets mis.');
                    }

                    quill.setText('');
                    quill.clipboard.dangerouslyPasteHTML(data.html);
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
        input.value = isEmpty ? '' : quill.getSemanticHTML();
        quill.container.classList.toggle('is-invalid', hasAttemptedSubmit && isRequired && isEmpty);
    };

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
