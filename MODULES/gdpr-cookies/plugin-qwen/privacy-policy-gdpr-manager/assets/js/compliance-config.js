/**
 * Konfiguracja Cookie Consent
 * @package PBMedia\PrivacyPolicyGDPRManager
 */

import './compliance-core.js';

const config = {
    categories: {
        necessary: {
            enabled: true,
            readOnly: true
        },
        analytics: {
            enabled: false
        }
    },
    language: {
        default: 'pl',
        autoDetect: 'browser',
        translations: {
            pl: {
                consentModal: {
                    title: 'Polityka Prywatności & RODO',
                    description: `W związku z przepisami o ochronie danych osobowych (RODO), utworzyliśmy klauzulę informacyjną. <a href="${window.ppgmConfig?.privacyUrl || '#'}">Więcej informacji</a>`,
                    acceptAllBtn: 'Akceptuj wszystko',
                    acceptNecessaryBtn: 'Odrzuć wszystko',
                    showPreferencesBtn: 'Zarządzaj preferencjami'
                },
                preferencesModal: {
                    title: 'Zarządzaj preferencjami cookie',
                    acceptAllBtn: 'Akceptuj wszystko',
                    acceptNecessaryBtn: 'Odrzuć wszystko',
                    savePreferencesBtn: 'Zaakceptuj wybór',
                    closeIconLabel: 'Zamknij',
                    sections: [
                        {
                            title: 'Wybierz pliki cookie',
                            description: 'Używamy plików cookie do nawigacji i funkcji witryny.'
                        },
                        {
                            title: 'Pliki niezbędne',
                            description: 'Te pliki są wymagane do działania witryny.',
                            linkedCategory: 'necessary'
                        },
                        {
                            title: 'Analityka',
                            description: 'Pliki do analizy ruchu (anonimowe).',
                            linkedCategory: 'analytics'
                        },
                        {
                            title: 'Kontakt',
                            description: `Pytania? <a href="mailto:${window.ppgmConfig?.contactEmail || ''}">Skontaktuj się z nami</a>`
                        }
                    ]
                }
            }
        }
    }
};

CookieConsent.run(config);