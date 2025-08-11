import React, { useEffect, useState } from "react";
import LocaleSelector from "./LocaleSelector";
import { t, setLocale, getLocale, fetchLocales, loadTranslations } from "./i18n";

const fetchSettings = async () => {
  const res = await fetch("/wp-json/wpscalerpro/v1/settings");
  return res.json();
};

const saveSettings = async (settings) => {
  const res = await fetch("/wp-json/wpscalerpro/v1/settings", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(settings),
  });
  return res.json();
};

export default function App() {
  const [apiKey, setApiKey] = useState("");
  const [locale, setLocaleState] = useState(getLocale());
  const [locales, setLocales] = useState({});
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(true);

  // Çevirileri yükle
  const updateLocale = async (newLocale) => {
    setLocale(newLocale);
    setLocaleState(newLocale);
    await loadTranslations(newLocale);
  };

  useEffect(() => {
    (async () => {
      const data = await fetchSettings();
      setApiKey(data.api_key || "");
      await updateLocale(data.locale || "en");
      const locs = await fetchLocales();
      setLocales(locs);
      setLoading(false);
    })();
    // eslint-disable-next-line
  }, []);

  // Locale değiştiğinde çevirileri yükle
  useEffect(() => {
    (async () => {
      await loadTranslations(locale);
    })();
  }, [locale]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setMessage("");
    setError("");
    try {
      const result = await saveSettings({ api_key: apiKey, locale });
      if (result.success) {
        setMessage(result.message || t("settings_saved"));
        await updateLocale(locale);
      } else {
        setError(result.message || "Error");
      }
    } catch (err) {
      setError("Error saving settings");
    }
  };

  if (loading) return <div>Loading...</div>;

  return (
    <div className="wpsp-admin-wrap">
      <h1>{t("plugin_name")}</h1>
      <form onSubmit={handleSubmit}>
        <div className="wpsp-form-row">
          <label htmlFor="wpsp_api_key">{t("api_key")}</label>
          <input
            id="wpsp_api_key"
            type="text"
            value={apiKey}
            onChange={(e) => setApiKey(e.target.value)}
            autoComplete="off"
          />
          <div className="wpsp-desc">{t("api_key_desc")}</div>
        </div>
        <div className="wpsp-form-row">
          <label htmlFor="wpsp_locale">{t("language")}</label>
          <LocaleSelector
            value={locale}
            onChange={async (val) => {
              setLocaleState(val);
              await updateLocale(val);
            }}
            locales={locales}
          />
          <div className="wpsp-desc">{t("language_desc")}</div>
        </div>
        {message && <div className="wpsp-message">{message}</div>}
        {error && <div className="wpsp-error">{error}</div>}
        <button type="submit" className="wpsp-btn-primary">
          {t("save")}
        </button>
      </form>
    </div>
  );
}
