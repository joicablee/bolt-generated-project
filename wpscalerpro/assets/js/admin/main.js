// Entry point for React admin app (bundled by Vite)
import React from "react";
import { createRoot } from "react-dom/client";
import App from "./App";

const root = document.getElementById("wpsp-admin-root");
if (root) {
  createRoot(root).render(<App />);
}
