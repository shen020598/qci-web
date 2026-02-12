import en from "../i18n/en.json";
import zh from "../i18n/zh.json";
import th from "../i18n/th.json";

const dictionaries: Record<string, any> = { en, zh, th };

export function getLang(lang?: string) {
  return dictionaries[lang ?? "en"] ? lang : "en";
}

export function t(lang: string) {
  return dictionaries[lang];
}

/**
 * Generate a locale-aware URL path
 * @param path - The path without locale (e.g., "/about")
 * @param locale - The current locale (e.g., "en", "th", "zh")
 * @returns The path with locale prefix if not default locale
 */
export function localePath(path: string, locale?: string) {
  // Default locale (en) doesn't need prefix
  if (!locale || locale === "en") {
    return path;
  }
  // Add locale prefix for non-default locales
  return `/${locale}${path}`;
}
