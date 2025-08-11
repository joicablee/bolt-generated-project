import React from "react";
import { getAvailableLocales } from "./i18n";

export default function LocaleSelector({ locale, setLocale }) {
  return (
    <div style={{ marginBottom: 16 }}>
      <label htmlFor="wpsp-locale" style={{ marginRight: 8, fontWeight: 500 }}>
        🌐
      </label>
      <select
        id="wpsp-locale"
        value={locale}
        onChange={e => setLocale(e.target.value)}
        style={{ padding: 4, borderRadius: 4 }}
      >
        {getAvailableLocales().map(l => (
          <option key={l} value={l}>
            {l === "tr" ? "Türkçe" : l === "en" ? "English" : l}
          </option>
        ))}
      </select>
    </div>
  );
}
