import { SET_COUNTRIES, SET_COUNTRY } from "@/actions/project/stat/map";

/**
 * Stat map Store state
 */
export const state = {
    statMapCountries: [],
    statMapCountry: null,
};

/**
 * Stat map Store Mutations
 */
export const mutations = {
    /**
     * Set stat map countries
     *
     * @param {*} state
     * @returns
     */
    [SET_COUNTRIES](state, countries) {
        state.project.statMapCountries = countries;
    },

    /**
     * Set stat map country
     *
     * @param {*} state
     * @returns
     */
    [SET_COUNTRY](state, country) {
        state.project.statMapCountry = country;
    },
};

/**
 * Stat map Store Getters
 */
export const getters = {
    /**
     * Get list of stat map countries
     *
     * @param {*} state
     * @returns
     */
    statMapCountries: (state) =>
        state.project &&
        state.project.statMapCountries &&
        Array.isArray(state.project.statMapCountries)
            ? state.project.statMapCountries
            : [],

    /**
     *
     * @param {*} state
     * @returns
     */
    statMapCountry: (state) =>
        state.project && state.project.statMapCountry
            ? state.project.statMapCountry
            : null,
};

/**
 * Store
 */
export default {
    state,
    mutations,
    getters,
};
