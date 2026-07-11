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
