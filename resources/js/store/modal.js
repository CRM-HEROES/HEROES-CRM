import { OPEN_MODAL, CLOSE_MODAL, CLOSE_MODALS } from "@/actions/modal";

/**
 * Modal Store state
 */
const state = {
    modals: [],
};

/**
 * Modal Store Mutations
 */
const mutations = {
    /**
     * Open specific modal
     *
     * @param {*} state
     * @returns
     */
    [OPEN_MODAL](state, modal) {
        if (
            state.modals.length == 0 ||
            state.modals[state.modals.length - 1] != modal
        ) {
            state.modals = [...state.modals, modal];
        }
    },

    /**
     * Close modal on top
     *
     * @param {*} state
     * @returns
     */
    [CLOSE_MODAL](state) {
        state.modals = state.modals.slice(0, state.modals.length - 1);
    },

    /**
     * Close all modals
     *
     * @param {*} state
     * @returns
     */
    [CLOSE_MODALS](state) {
        state.modals = [];
    },
};

/**
 * Modal Store Getters
 */
const getters = {
    /**
     * Get modals
     *
     * @param {*} state
     * @returns
     */
    modals: (state) => state.modals,

    /**
     * Get modal open
     *
     * @param {*} state
     * @returns
     */
    modalOpen: (state) => (name) => state.modals.indexOf(name) >= 0,

    /**
     * Get modal index
     *
     * @param {*} state
     * @returns
     */
    modalIndex: (state) => (name) => state.modals.indexOf(name),
};

/**
 * Store
 */
export default {
    state,
    mutations,
    getters,
};
