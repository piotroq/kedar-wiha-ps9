import '$plugins_url = plugins_url();/custom/cookieconsent.umd.js';

/**
 * All config. options available here:
 * https://cookieconsent.orestbida.com/reference/configuration-reference.html
 */
CookieConsent.run({

    categories: {
        necessary: {
            enabled: true,  // this category is enabled by default
            readOnly: true  // this category cannot be disabled
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
                    description: 'W związku z tym, iż 25 maja 2018 roku zaczęły obowiązywać nowe przepisy o ochronie danych osobowych, w tym Rozporządzenie Parlamentu Europejskiego i Rady (UE) 2016/679 w sprawie ochrony danych osobowych osób fizycznych w związku z przetwarzaniem danych osobowych i w sprawie swobodnego przepływu takich danych oraz uchylenia dyrektywy 95/46/WE oraz Ustawa z dnia 10 maja 2018 roku o ochronie danych osobowych, utworzyliśmy klauzulę informacyjną w zakresie ochrony danych osobowych. Z treści naszej klauzuli dowiedzą się Państwo w jakim zakresie, celu oraz formie przetwarzamy Państwa dane osobowe jako administrator danych. <a href="$privacy_policy_page_link = get_the_privacy_policy_link();">Więcej informacji</a>',
                    acceptAllBtn: 'Akceptuj wszystko',
                    acceptNecessaryBtn: 'Odrzuć wszystko',
                    showPreferencesBtn: 'Zarządzaj indywidualnymi preferencjami'
                },
                preferencesModal: {
                    title: 'Zarządzaj preferencjami dotyczącymi plików cookie',
                    acceptAllBtn: 'Akceptuj wszystko',
                    acceptNecessaryBtn: 'Odrzuć wszystko',
                    savePreferencesBtn: 'Zaakceptuj bieżący wybór',
                    closeIconLabel: 'Zamknij okno',
                    sections: [
                        {
                            title: 'Wybierz, które pliki cookie chcesz zaakceptować?',
                            description: 'Używamy plików cookie, aby pomóc użytkownikom w sprawnej nawigacji i wykonywaniu określonych funkcji. Szczegółowe informacje na temat wszystkich plików cookie odpowiadających poszczególnym kategoriom zgody znajdują się poniżej. Pliki cookie sklasyfikowane jako „niezbędne” są przechowywane w przeglądarce użytkownika, ponieważ są niezbędne do włączenia podstawowych funkcji witryny. Korzystamy również z plików cookie innych firm, które pomagają nam analizować sposób korzystania ze strony przez użytkowników, a także przechowywać preferencje użytkownika oraz dostarczać mu istotnych dla niego treści i reklam. Tego typu pliki cookie będą przechowywane w przeglądarce tylko za uprzednią zgodą użytkownika. Można włączyć lub wyłączyć niektóre lub wszystkie te pliki cookie, ale wyłączenie niektórych z nich może wpłynąć na jakość przeglądania.'
                        },
                        {
                            title: 'Pliki cookie niezbędne do działania',
                            description: 'Te pliki cookie są niezbędne do prawidłowego funkcjonowania witryny i nie można ich wyłączyć.',

                            //this field will generate a toggle linked to the 'necessary' category
                            linkedCategory: 'necessary'
                        },
                        {
                            title: 'Wydajność i analityka',
                            description: 'Te pliki cookie zbierają informacje o tym, jak korzystasz z naszej witryny. Wszystkie dane są anonimizowane i nie mogą być użyte do zidentyfikowania Ciebie.',
                            linkedCategory: 'analytics'
                        },
                        {
                            title: 'Więcej informacji',
                            description: 'W przypadku pytań dotyczących polityki dotyczącej plików cookie i Twoich wyborów, prosimy o kontakt: <a href="mailto:$email_contact_for_cookie_policy">kontakt z nami</a>'
                        }
                    ]
                }
            }
        }
    }
});
