import React from "react";
import { createRoot } from "react-dom/client";
import App from "./App.jsx";
import "./admin.css";

const container = document.getElementById("wpsp-admin-root");
if (container) {
  const root = createRoot(container);
  root.render(<App />);
}
