import './bootstrap';
import 'bootstrap-icons/font/bootstrap-icons.css';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const initializeConfirmForms = () => {
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const message = form.dataset.confirm;
        if (!message) {
            return;
        }

        if (!window.confirm(message)) {
            event.preventDefault();
        }
    });
};

const initializeSubmitLoadingState = () => {
    document.addEventListener('submit', (event) => {
        if (event.defaultPrevented) {
            return;
        }

        const form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        submitButtons.forEach((button) => {
            if (button.dataset.loadingApplied === 'true') {
                return;
            }

            button.dataset.loadingApplied = 'true';
            button.dataset.originalLabel = button.tagName === 'INPUT' ? button.value : button.innerHTML;
            button.disabled = true;

            const loadingText = button.dataset.loadingText || 'Memproses...';
            if (button.tagName === 'INPUT') {
                button.value = loadingText;
                return;
            }

            button.innerHTML = `<span class="inline-flex items-center gap-2"><i class="bi bi-arrow-repeat animate-spin"></i>${loadingText}</span>`;
        });
    });
};

const initializeRegionFields = () => {
    const forms = document.querySelectorAll('[data-region-form]');
    if (forms.length === 0) {
        return;
    }

    forms.forEach((form) => {
        const regionSelect = form.querySelector('[data-region-select]');
        if (!(regionSelect instanceof HTMLSelectElement)) {
            return;
        }

        const groups = form.querySelectorAll('[data-region-option]');

        const toggle = () => {
            groups.forEach((group) => {
                const isActive = group.dataset.regionOption === regionSelect.value;
                group.classList.toggle('hidden', !isActive);

                group.querySelectorAll('input, select').forEach((control) => {
                    control.disabled = !isActive;
                });
            });
        };

        regionSelect.addEventListener('change', toggle);
        toggle();
    });
};

const initializeTomSelect = async () => {
    const elements = document.querySelectorAll('[data-tom-select]');
    if (elements.length === 0) {
        return;
    }

    const [{ default: TomSelect }] = await Promise.all([
        import('tom-select'),
        import('tom-select/dist/css/tom-select.css'),
    ]);

    elements.forEach((element) => {
        if (!(element instanceof HTMLSelectElement) || element.dataset.tomSelectReady === 'true') {
            return;
        }

        let parsedOptions = {};
        const rawOptions = element.dataset.tomSelectOptions;
        if (rawOptions) {
            try {
                parsedOptions = JSON.parse(rawOptions);
            } catch (error) {
                console.warn('TomSelect options gagal diparse:', error);
            }
        }

        const defaultOptions = element.multiple
            ? {
                plugins: ['remove_button'],
                persist: false,
                create: false,
            }
            : {
                persist: false,
                create: false,
            };

        element.dataset.tomSelectReady = 'true';
        new TomSelect(element, { ...defaultOptions, ...parsedOptions });
    });
};

const initializeUI = async () => {
    initializeConfirmForms();
    initializeSubmitLoadingState();
    initializeRegionFields();
    await initializeTomSelect();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initializeUI();
    });
} else {
    initializeUI();
}
