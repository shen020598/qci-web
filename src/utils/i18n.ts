import en from "../data/en.json";
import zh from "../data/zh.json";
import th from "../data/th.json";

const dictionaries: Record<string, any> = { en, zh, th };

export function getLang(lang?: string) {
  return dictionaries[lang ?? "en"] ? lang : "en";
}

export function t(lang: string) {
  return dictionaries[lang];
}
