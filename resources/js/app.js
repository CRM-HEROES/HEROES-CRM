/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import "./bootstrap";
import { createApp } from "vue";
import Router from "@/router";
import store from "@/store";
import hello from "hellojs";
import i18n from "./i18n.js";
import tuto from "./utils/tuto";
import tooltip from "./utils/tooltip.js";

/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
 */

const app = createApp({});

const STORE_VERSION = "202528011202";

const storedVersion = localStorage.getItem("store_version");

if (storedVersion !== STORE_VERSION) {
    store.commit("RESET");
    localStorage.setItem("store_version", STORE_VERSION);
}

import Autocomplete from "./components/Autocomplete.vue";
import Buttons from "./components/Buttons.vue";
import CardMenu from "./components/CardMenu.vue";
import CardMenuList from "./components/CardMenuList.vue";
import Checkbox from "./components/Checkbox.vue";
// import CircularProgressBar from "./components/CircularProgressBar.vue";
import ColorPalette from "./components/ColorPalette.vue";
import Draggable from "vuedraggable";
import Flash from "./components/Flash.vue";
import FrameLayout from "./components/FrameLayout.vue";
import GoogleMapInput from "./components/GoogleMapInput.vue";
import HField from "./components/HField.vue";
import Icon from "./components/Icon.vue";
import Item from "./components/Item.vue";
import ItemList from "./components/ItemList.vue";
import Loading from "./components/Loading.vue";
import Modal from "./components/Modal.vue";
import Search from "./components/Search.vue";
import SearchDropdown from "./components/SearchDropdown.vue";
import Slide from "./components/Slide.vue";
import Tab from "./components/Tab.vue";
import TabLayout from "./components/TabLayout.vue";
import Tag from "./components/Tag.vue";
import TextEditor from "./components/TextEditor.vue";
import TreeLayout from "./components/TreeLayout.vue";
import Tooltip from "./components/Tooltip.vue";
import TutorialTooltip from "./components/TutorialTooltip.vue";
import VField from "./components/VField.vue";
import VirtualTable from "./components/VirtualTable.vue";
import ProtectedContent from "./components/ProtectedContent.vue";

app.component("autocomplete", Autocomplete);
app.component("buttons", Buttons);
app.component("card-menu", CardMenu);
app.component("card-menu-list", CardMenuList);
app.component("checkbox", Checkbox);
app.component("color-palette", ColorPalette);
app.component("checkbox", Checkbox);
app.component("draggable", Draggable);
app.component("flash", Flash);
app.component("frame-layout", FrameLayout);
app.component("google-map-input", GoogleMapInput);
app.component("h-field", HField);
app.component("icon", Icon);
app.component("item", Item);
app.component("item-list", ItemList);
app.component("loading", Loading);
app.component("modal", Modal);
app.component("protected-content", ProtectedContent);
app.component("search", Search);
app.component("search-dropdown", SearchDropdown);
app.component("slide", Slide);
app.component("tab", Tab);
app.component("tab-layout", TabLayout);
app.component("tag", Tag);
app.component("text-editor", TextEditor);
app.component("tree-layout", TreeLayout);
app.component("tooltip", Tooltip);
app.component("tutorial-tooltip", TutorialTooltip);
app.component("v-field", VField);
app.component("virtual-table", VirtualTable);

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// Object.entries(import.meta.glob('./**/*.vue', { eager: true })).forEach(([path, definition]) => {
//     app.component(path.split('/').pop().replace(/\.\w+$/, ''), definition.default);
// });

/**
 * Finally, we will attach the application instance to a HTML element with
 * an "id" attribute of "app". This element is included with the "auth"
 * scaffolding. Otherwise, you will need to add an element yourself.
 */

app.use(Router);
app.use(store);
app.use(i18n);
app.mount("#app");

hello.init(
    {
        google: "344393358836-ni9io17vhnldcelmnn9u7oisnr4bjot2.apps.googleusercontent.com",
    },
    {
        scope: "email",
        redirect_uri: location.origin,
    }
);

app.directive("tuto", tuto);
app.directive("tooltip", tooltip);
