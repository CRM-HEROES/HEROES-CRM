import {
    OPEN_SLIDE,
    OPEN_SUB_SLIDE,
    OPEN_LEFT_SLIDE,
    CLOSE_SLIDE,
    CLOSE_SLIDES,
} from "@/actions/slide";
import { SET_ASSIGNED_PHONE_COUNTRY_CODES } from "@/actions/project/user/setting";

/**
 * Slide Store state
 */
const state = {
    slides: [],
    leftSlides: [],
    assignedPhoneCountryCodes: {},
};

/**
 * Slide Store Mutations
 */
const mutations = {
    /**
     * Open specific slide
     *
     * @param {*} state
     * @returns
     */
    [OPEN_SLIDE](state, slide) {
        state.slides = [slide];
    },

    /**
     * Open specific slide
     *
     * @param {*} state
     * @returns
     */
    [OPEN_LEFT_SLIDE](state, slide) {
        state.leftSlides = [slide];
    },

    /**
     * Open sub slide
     *
     * @param {*} state
     * @returns
     */
    [OPEN_SUB_SLIDE](state, slide) {
        state.slides = [...state.slides.filter((s) => s != slide), slide];
    },

    /**
     * Close slide
     *
     * @param {*} state
     * @returns
     */
    [CLOSE_SLIDE](state, left) {
        if (left) {
            state.leftSlides = state.leftSlides.slice(
                0,
                state.leftSlides.length - 1
            );
        } else {
            state.slides = state.slides.slice(0, state.slides.length - 1);
        }
    },

    /**
     * Close all slides
     *
     * @param {*} state
     * @returns
     */
    [CLOSE_SLIDES](state) {
        state.slides = [];
        state.leftSlides = [];
    },

    [SET_ASSIGNED_PHONE_COUNTRY_CODES](state, { project, user, countries }) {
        const key = `${project}:${user}`;
        state.assignedPhoneCountryCodes = {
            ...state.assignedPhoneCountryCodes,
            [key]: countries,
        };
    },
};

/**
 * Slide Store Getters
 */
const getters = {
    /**
     * Get slides
     *
     * @param {*} state
     * @returns
     */
    slides: (state) => state.slides,

    /**
     * Get left slides
     *
     * @param {*} state
     * @returns
     */
    leftSlides: (state) => state.leftSlides,

    assignedPhoneCountryCodes: (state) => (project, user) =>
        state.assignedPhoneCountryCodes[`${project}:${user}`] || [],

    /**
     * Get slide open
     *
     * @param {*} state
     * @returns
     */
    slideOpen: (state) => (name) =>
        state.slides.length > 0 &&
        state.slides[state.slides.length - 1] == name,
    // state.slides.indexOf(name) >= 0,

    /**
     * Get slide open
     *
     * @param {*} state
     * @returns
     */
    leftSlideOpen: (state) => (name) =>
        state.leftSlides.length > 0 &&
        state.leftSlides[state.leftSlides.length - 1] == name,
    /**
     * Get modal index
     *
     * @param {*} state
     * @returns
     */
    slideIndex: (state) => (name) => state.slides.indexOf(name),

    /**
     * Get modal index
     *
     * @param {*} state
     * @returns
     */
    leftSlideIndex: (state) => (name) => state.leftSlides.indexOf(name),

    /**
     * Get sub slide open
     *
     * @param {*} state
     * @returns
     */
    subSlideOpen: (state) => state.slides.length > 1,
};

/**
 * Store
 */
export default {
    state,
    mutations,
    getters,
};
