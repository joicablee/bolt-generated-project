import React from "react";

export default function LocaleSelector({ value, onChange, locales }) {
  return (
    <select
      id="wpsp_locale"
      value={value}
      onChange={(e) => onChange(e.target.value)}
    >
      {Object.entries(locales).map(([code, label]) => (
        <option key={code} value={code}>
          {label}
        </option>
      ))}
    </select>
  );
}
