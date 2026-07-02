import { createI18n } from "vue-i18n";
import store from "@/store";

import ar from "./lang/ar.json";
import en from "./lang/en.json";
import es from "./lang/es.json";
import fr from "./lang/fr.json";
import hi from "./lang/hi.json";
import it from "./lang/it.json";
import pt from "./lang/pt.json";
import ru from "./lang/ru.json";
import zh_CN from "./lang/zh_CN.json";

export default createI18n({
    locale: store.getters.locale,
    fallbackLocale: "fr",
    messages: { ar, fr, en, es, it, hi, pt, ru, zh_CN },
    warnHtmlInMessage: "off",
});
