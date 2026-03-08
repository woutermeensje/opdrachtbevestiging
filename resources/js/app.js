import './bootstrap';

const kvkForm = document.querySelector('[data-kvk-form]');

if (kvkForm) {
    const stepForm = kvkForm.matches('[data-step-form]') ? kvkForm : null;
    const stepPanels = stepForm ? Array.from(stepForm.querySelectorAll('[data-step-panel]')) : [];
    const stepIndicators = stepForm ? Array.from(stepForm.querySelectorAll('[data-step-indicator]')) : [];
    const kvkInput = kvkForm.querySelector('[data-kvk-number]');
    const lookupButton = kvkForm.querySelector('[data-kvk-lookup]');
    const feedback = kvkForm.querySelector('[data-kvk-feedback]');
    let currentStep = Number(stepForm?.dataset.initialStep ?? 1);

    const syncSteps = () => {
        if (!stepForm) {
            return;
        }

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
        const currentPanel = stepForm?.querySelector(`[data-step-panel="${currentStep}"]`);

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

    lookupButton?.addEventListener('click', async () => {
        const kvkNumber = kvkInput?.value.replace(/\D/g, '') ?? '';
        const lookupUrl = lookupButton.dataset.kvkUrl;

        if (kvkNumber.length !== 8 || !lookupUrl) {
            setFeedback('Vul een geldig KVK-nummer van 8 cijfers in.', 'error');
            return;
        }

        lookupButton.disabled = true;
        setFeedback('Bedrijfsgegevens worden opgehaald...', 'loading');

        try {
            const response = await window.axios.post(lookupUrl, {
                kvk_number: kvkNumber,
            });

            fillFields(response.data.data ?? {});
            setFeedback('Bedrijfsgegevens zijn ingevuld. Controleer de gegevens voor je registreert.', 'success');
        } catch (error) {
            const message = error.response?.data?.message
                ?? 'Het ophalen van KVK-gegevens is niet gelukt.';

            setFeedback(message, 'error');
        } finally {
            lookupButton.disabled = false;
        }
    });

    stepForm?.querySelectorAll('[data-step-next]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!validateCurrentStep()) {
                return;
            }

            currentStep = Math.min(currentStep + 1, stepPanels.length);
            syncSteps();
        });
    });

    stepForm?.querySelectorAll('[data-step-prev]').forEach((button) => {
        button.addEventListener('click', () => {
            currentStep = Math.max(currentStep - 1, 1);
            syncSteps();
        });
    });

    syncSteps();
}
