import React, { useState, useEffect } from "react";
import { t } from "./i18n";
import LocaleSelector from "./LocaleSelector";

function InfoBox({ children }) {
  return (
    <div
      style={{
        background: "#f4f8ff",
        border: "1px solid #b3d1ff",
        borderRadius: 6,
        padding: "12px 16px",
        marginBottom: 20,
        color: "#234",
        fontSize: 15,
        lineHeight: 1.6,
      }}
      aria-label={t("api_key_info_aria")}
    >
      {children}
    </div>
  );
}

export default function App() {
  const [locale, setLocale] = useState("tr");
  const [apiKey, setApiKey] = useState("");
  const [show, setShow] = useState(false);
  const [status, setStatus] = useState("");
  const [processing, setProcessing] = useState(false);

  useEffect(() => {
    fetch(`/wp-json/wpscalerpro/v1/apikey?locale=${locale}`)
      .then(r => r.json())
      .then(data => {
        setApiKey(data.apiKey || "");
        setStatus(data.message || "");
      });
  }, [locale]);

  const handleShow = () => setShow(s => !s);

  const handleCopy = () => {
    navigator.clipboard.writeText(apiKey.replace(/\*/g, "")).then(() => {
      setStatus(t("copied", locale));
      setTimeout(() => setStatus(""), 1200);
    });
  };

  const handleDelete = () => {
    setProcessing(true);
    fetch(`/wp-json/wpscalerpro/v1/apikey?locale=${locale}`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ apiKey: "" }),
    })
      .then(r => r.json())
      .then(data => {
        setApiKey("");
        setStatus(data.message || "");
        setProcessing(false);
      });
  };

  const handleSave = e => {
    e.preventDefault();
    const newKey = e.target.elements.apiKey.value.trim();
    if (!newKey) return;
    setProcessing(true);
    fetch(`/wp-json/wpscalerpro/v1/apikey?locale=${locale}`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ apiKey: newKey }),
    })
      .then(async r => {
        const data = await r.json();
        setApiKey(data.apiKey || "");
        setStatus(data.message || "");
        setProcessing(false);
      });
  };

  return (
    <div style={{ maxWidth: 480, margin: "32px auto", fontFamily: "Inter, Arial, sans-serif" }}>
      <LocaleSelector locale={locale} setLocale={setLocale} />
      <h2 style={{ marginBottom: 4 }}>{t("api_key_management", locale)}</h2>
      <div style={{ color: "#666", marginBottom: 18, fontSize: 15 }}>
        {t("api_key_management_subtitle", locale)}
      </div>
      <InfoBox>
        <b>{t("what_is_api_key", locale)}</b>
        <br />
        {t("api_key_desc", locale)}
        <br />
        <b>{t("how_to_get", locale)}</b>{" "}
        <a href="https://panel.wpscaler.com" target="_blank" rel="noopener noreferrer">
          {t("wpscalerpro_panel", locale)}
        </a>
        . {t("create_api_key_here", locale)}
        <br />
        <b>{t("security", locale)}</b> {t("api_key_security", locale)}
      </InfoBox>
      <form onSubmit={handleSave} style={{ marginBottom: 24 }}>
        <div style={{ marginBottom: 10 }}>
          <label style={{ fontWeight: 500 }}>{t("current_api_key", locale)}:</label>
          <div style={{ display: "flex", alignItems: "center", gap: 8, marginTop: 4 }}>
            <input
              type={show ? "text" : "password"}
              value={apiKey}
              readOnly
              style={{
                width: "100%",
                fontSize: 15,
                padding: "6px 8px",
                border: "1px solid #bbb",
                borderRadius: 4,
                background: "#f9f9f9",
                letterSpacing: 2,
              }}
              aria-label={t("current_api_key", locale)}
            />
            <button
              type="button"
              onClick={handleShow}
              style={{
                fontSize: 13,
                padding: "4px 8px",
                borderRadius: 4,
                border: "1px solid #bbb",
                background: "#fff",
                cursor: "pointer",
              }}
              aria-label={show ? t("hide", locale) : t("show", locale)}
            >
              {show ? t("hide", locale) : t("show", locale)}
            </button>
            <button
              type="button"
              onClick={handleCopy}
              style={{
                fontSize: 13,
                padding: "4px 8px",
                borderRadius: 4,
                border: "1px solid #bbb",
                background: "#fff",
                cursor: "pointer",
              }}
              aria-label={t("copy", locale)}
            >
              {t("copy", locale)}
            </button>
            <button
              type="button"
              onClick={handleDelete}
              style={{
                fontSize: 13,
                padding: "4px 8px",
                borderRadius: 4,
                border: "1px solid #e55",
                background: "#fff0f0",
                color: "#b00",
                cursor: "pointer",
              }}
              aria-label={t("delete_api_key", locale)}
              disabled={processing}
            >
              {t("delete", locale)}
            </button>
          </div>
        </div>
        <div style={{ marginBottom: 10 }}>
          <label htmlFor="apiKey" style={{ fontWeight: 500 }}>
            {t("new_api_key", locale)}
          </label>
          <input
            id="apiKey"
            name="apiKey"
            type="text"
            placeholder={t("enter_new_key", locale)}
            style={{
              width: "100%",
              fontSize: 15,
              padding: "6px 8px",
              border: "1px solid #bbb",
              borderRadius: 4,
              marginTop: 4,
            }}
            disabled={processing}
            autoComplete="off"
          />
        </div>
        <button
          type="submit"
          style={{
            fontSize: 15,
            padding: "7px 18px",
            borderRadius: 4,
            border: "1px solid #2a7",
            background: "#eaffea",
            color: "#185",
            fontWeight: 600,
            cursor: "pointer",
            marginTop: 4,
          }}
          disabled={processing}
        >
          {processing ? t("processing", locale) : t("save", locale)}
        </button>
      </form>
      {status && (
        <div
          style={{
            background: status.includes("hata") || status.toLowerCase().includes("error")
              ? "#fff0f0"
              : "#f0fff0",
            color: status.includes("hata") || status.toLowerCase().includes("error")
              ? "#b00"
              : "#185",
            border: "1px solid #ddd",
            borderRadius: 4,
            padding: "8px 12px",
            marginBottom: 12,
            fontSize: 15,
          }}
          role="status"
        >
          {status}
        </div>
      )}
    </div>
  );
}
