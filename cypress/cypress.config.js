import { defineConfig } from "cypress";

export default defineConfig({
  component: {
    devServer: {
      framework: "vue",
      bundler: "vite",
    },
  },

  e2e: {
    baseUrl: process.env.CYPRESS_BASE_URL || "http://localhost:8000",
    viewportWidth: 1280,
    viewportHeight: 720,
    setupNodeEvents(on, config) {
      on("task", {
        log(args) {
          console.log(...args);
          return null;
        }
      });
    },
  },
});
