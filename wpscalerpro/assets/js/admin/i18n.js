import tr from '../../../languages/tr.json';
import en from '../../../languages/en.json';

const locales = { tr, en };

export function t(key, locale = 'tr') {
  return locales[locale]?.[key] || key;
}

export function getAvailableLocales() {
  return Object.keys(locales);
}
