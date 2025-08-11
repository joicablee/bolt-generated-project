let currentLocale = "en";
let translations = {};

export function setLocale(locale) {
  currentLocale = locale;
}

export function getLocale() {
  return currentLocale;
}

export async function fetchLocales() {
  const res = await fetch("/wp-json/wpscalerpro/v1/locales");
  return res.json();
}

export async function loadTranslations(locale) {
  try {
    const res = await fetch(`/wp-content/plugins/wpscalerpro/languages/${locale}.json`);
    translations = await res.json();
  } catch (e) {
    translations = {};
  }
}

export function t(key) {
  return translations[key] || key;
}
