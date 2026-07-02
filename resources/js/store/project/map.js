import {
    SET_MAP_PROSPECT,
    SET_MAP_COLOR_BY_LABEL,
    ADD_MAP_VEHICLE,
    REMOVE_MAP_VEHICLE,
} from "@/actions/project/map";

/**
 * group Store state
 */
export const state = {
    mapProspect: null,
    mapColorByLabel: null,
    mapVehicles: [],
};

/**
 * label Store Mutations
 */
export const mutations = {
    /**
     * Set labels
     *
     * @param {*} state
     * @returns
     */
    [SET_MAP_PROSPECT](state, prospect) {
        state.project.mapProspect = prospect;
    },

    /**
     * Set labels
     *
     * @param {*} state
     * @returns
     */
    [SET_MAP_COLOR_BY_LABEL](state, category) {
        state.project.mapColorByLabel = category;
    },

    /**
     * Add vehicle
     *
     * @param {*} state
     * @returns
     */
    [ADD_MAP_VEHICLE](state, vehicle) {
        state.project.mapVehicles = [...state.project.mapVehicles, vehicle];
    },

    /**
     * Remove vehicle
     *
     * @param {*} state
     * @returns
     */
    [REMOVE_MAP_VEHICLE](state, vehicle) {
        state.project.mapVehicles = state.project.mapVehicles.filter(
            (v) => v.id != vehicle.id
        );
    },
};

/**
 * Map Store Getters
 */
export const getters = {
    /**
     * Get current map prospect
     *
     * @param {*} state
     * @returns
     */
    mapProspect: (state) =>
        state.project && state.project.mapProspect
            ? state.project.mapProspect
            : null,

    /**
     * Get current color by label
     *
     * @param {*} state
     * @returns
     */
    mapColorByLabel: (state) =>
        state.project && state.project.mapColorByLabel
            ? state.project.mapColorByLabel
            : null,

    /**
     * Get user positions
     *
     * @param {*} state
     * @returns
     */
    mapVehicles: (state) =>
        state.project && state.project.mapVehicles
            ? state.project.mapVehicles
            : [],
};

/**
 * Store
 */
export default {
    state,
    mutations,
    getters,
};
