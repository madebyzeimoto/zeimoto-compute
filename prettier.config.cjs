// ==========================================================
// Tooling :: Prettier
// ==========================================================
//
// Defines repository-wide formatting behavior for supported
// frontend, configuration, and documentation files.
//
// ==========================================================

/** @type {import("prettier").Config} */
module.exports = {
  printWidth: 120,
  tabWidth: 2,
  useTabs: false,
  semi: false,
  singleQuote: false,
  jsxSingleQuote: false,
  trailingComma: "es5",
  bracketSpacing: true,
  arrowParens: "always",
  htmlWhitespaceSensitivity: "ignore",
  proseWrap: "preserve",
  endOfLine: "lf",
}
