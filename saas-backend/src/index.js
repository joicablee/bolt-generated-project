import express from "express";
import cors from "cors";

const app = express();
app.use(cors());
app.use(express.json());

app.get("/api/health", (req, res) => {
  res.json({ status: "ok", message: "WpscalerPro SaaS API is running." });
});

// Örnek: Ayarları almak için endpoint
app.get("/api/settings", (req, res) => {
  // Burada gerçek veritabanı yerine örnek veri dönülüyor
  res.json({
    locale: "en",
    apiKey: "demo-key"
  });
});

const PORT = process.env.PORT || 4000;
app.listen(PORT, () => {
  console.log(`WpscalerPro SaaS API listening on port ${PORT}`);
});
