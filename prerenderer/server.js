const prerender = require("prerender");

const whitelist = require("prerender/lib/plugins/whitelist");

const useWhitelist = process.env.ALLOWED_DOMAINS ? true : false;

if (useWhitelist)
  process.env.ALLOWED_DOMAINS =
    process.env.ALLOWED_DOMAINS +
    "," +
    process.env.ALLOWED_DOMAINS.split(",")
      .map((el) => "www." + el)
      .join(",");

const server = prerender({
  port: process.env.PORT ?? 3000, // default port is 3000
  pageLoadTimeout: 20 * 1000, // milliseconds
  chromeLocation:
    process.env.CHROME_LOCATION ?? "/usr/bin/google-chrome-stable",
  chromeFlags: [
    "--headless",
    "--disable-gpu",
    "--remote-debugging-port=9222",
    "--hide-scrollbars",
  ],
});

console.warn(
  "Allowed domains: " + (useWhitelist ? process.env.ALLOWED_DOMAINS : "all"),
);

if (useWhitelist) server.use(whitelist);

server.start();

// http://localhost:3001/render?url=https://www.example.com/
