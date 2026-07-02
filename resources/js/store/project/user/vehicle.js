import UserVehicleService from "@/apis/project/user/vehicle";

import {
    FETCH_USER_VEHICLES,
    SET_USER_VEHICLES,
    SHOW_USER_VEHICLE,
    SET_USER_VEHICLE,
    ADD_USER_VEHICLE,
    UPDATE_USER_VEHICLE,
    REMOVE_USER_VEHICLE,
} from "@/actions/project/user/vehicle";

/**
 * UserVehicle Store state
 */
export const state = {
    vehicles: [],
    vehicle: null,
};

/**
 * UserVehicle Store Actions
 */
export const actions = {
    /**
     * Fetch user vehicles
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_USER_VEHICLES](context, params) {
        const { data } = await UserVehicleService.get(
            context.state.project.slug,
            context.state.project.user.id,
            params
        );
        context.commit(SET_USER_VEHICLES, data);
        return data;
    },

    /**
     * Show user vehicle
     *
     * @param {*} context
     * @returns
     */
    async [SHOW_USER_VEHICLE](context, slug) {
        const { data } = await UserVehicleService.get(
            context.state.project.slug,
            context.state.project.user.id,
            slug
        );
        context.commit(SET_USER_VEHICLE, data);
        return data;
    },

    /**
     * Add user vehicle
     *
     * @param {*} context
     * @param {Object} params
     * @returns userVehicle
     */
    async [ADD_USER_VEHICLE](context, vehicle) {
        const { data } = await UserVehicleService.store(
            context.state.project.slug,
            context.state.project.user.id,
            vehicle
        );
        context.commit(ADD_USER_VEHICLE, data);
    },

    /**
     * Update user vehicle
     *
     * @param {*} context
     * @param {Object} params
     * @returns userVehicle
     */
    async [UPDATE_USER_VEHICLE](context, vehicle) {
        context.commit(UPDATE_USER_VEHICLE, vehicle);
        await UserVehicleService.update(
            context.state.project.slug,
            context.state.project.user.id,
            vehicle.id,
            vehicle
        );
    },

    /**
     * Remove user vehicle
     *
     * @param {*} context
     * @param {Number} params user vehicle id
     * @returns userVehicle
     */
    async [REMOVE_USER_VEHICLE](context, vehicle) {
        context.commit(REMOVE_USER_VEHICLE, vehicle);
        await UserVehicleService.destroy(
            context.state.project.slug,
            context.state.project.user.id,
            vehicle.id
        );
    },
};

/**
 * User vehicle Store Mutations
 */
export const mutations = {
    /**
     * Set user vehicles
     *
     * @param {*} state
     * @returns
     */
    [SET_USER_VEHICLES](state, vehicles) {
        state.project.user.vehicles = vehicles;
    },

    /**
     * Set user vehicle
     *
     * @param {*} state
     * @returns
     */
    [SET_USER_VEHICLE](state, vehicle) {
        state.project.user.vehicle = vehicle;
    },

    /**
     * Add user vehicle
     *
     * @param {*} state
     * @param {Number} vehicle to append to user vehicles list
     */
    [ADD_USER_VEHICLE](state, vehicle) {
        state.project.user.vehicles = [
            ...(state.project.user.vehicles ? state.project.user.vehicles : []),
            vehicle,
        ];
    },

    /**
     * Update user vehicle
     *
     * @param {*} context
     * @param {Number} params user vehicle id
     */
    [UPDATE_USER_VEHICLE](state, vehicle) {
        state.project.vehicles = state.project.vehicles.map((o) =>
            o.id == vehicle.id ? { ...o, ...vehicle } : o
        );
    },

    /**
     * Remove user vehicle
     *
     * @param {*} context
     * @param {Number} params user vehicle id
     */
    [REMOVE_USER_VEHICLE](state, vehicle) {
        state.project.user.vehicles = (
            state.project.user.vehicles ? state.project.user.vehicles : []
        ).filter((o) => o.id != vehicle.id);
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },
};

/**
 * User vehicle Store Getters
 */
export const getters = {
    /**
     * Get list of user vehicles
     *
     * @param {*} state
     * @returns
     */
    userVehicles: (state) =>
        state.project &&
        state.project.user &&
        state.project.user.vehicles &&
        Array.isArray(state.project.user.vehicles)
            ? state.project.user.vehicles
            : [],

    /**
     * Get user vehicle
     *
     * @param {*} state
     * @returns
     */
    userVehicle: (state) =>
        state.project && state.project.user && state.project.user.vehicle
            ? state.project.user.vehicle
            : null,
};

/**
 * Store
 */
export default {
    state,
    actions,
    mutations,
    getters,
};
