import VehicleService from "@/apis/project/vehicle";

import {
    FETCH_VEHICLES,
    SET_VEHICLES,
    SHOW_VEHICLE,
    SET_VEHICLE,
    ADD_VEHICLE,
    UPDATE_VEHICLE,
    REMOVE_VEHICLE,
} from "@/actions/project/vehicle";

/**
 * vehicle Store state
 */
export const state = {
    vehicles: [],
};

/**
 * vehicle Store Actions
 */
const actions = {
    /**
     * Fetch vehicles
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_VEHICLES](context, params) {
        const { data } = await VehicleService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_VEHICLES, data);
        return data;
    },

    /**
     * Add vehicle
     *
     * @param {*} context
     * @param {Object} params
     * @returnsVehicle
     */
    async [ADD_VEHICLE](context, vehicle) {
        const { data } = await VehicleService.store(
            context.state.project.slug,
            vehicle
        );
        context.commit(ADD_VEHICLE, data);
    },

    /**
     * Update vehicle
     *
     * @param {*} context
     * @param {Object} params
     * @returnsVehicle
     */
    async [UPDATE_VEHICLE](context, vehicle) {
        context.commit(UPDATE_VEHICLE, vehicle);
        await VehicleService.update(
            context.state.project.slug,
            vehicle.id,
            vehicle
        );
    },

    /**
     * Remove vehicle
     *
     * @param {*} context
     * @param {Number} params vehicle id
     * @returnsVehicle
     */
    async [REMOVE_VEHICLE](context, vehicle) {
        context.commit(REMOVE_VEHICLE, vehicle);
        await VehicleService.destroy(context.state.project.slug, vehicle.id);
    },

    /**
     * Fetch vehicles
     *
     * @param {*} context
     * @returns
     */
    async [SHOW_VEHICLE](context, slug) {
        const { data } = await VehicleService.show(
            context.state.project.slug,
            slug
        );
        context.commit(SET_VEHICLE, data);
        return data;
    },
};

/**
 * vehicle Store Mutations
 */
const mutations = {
    /**
     * Set vehicles
     *
     * @param {*} state
     * @returns
     */
    [SET_VEHICLES](state, vehicles) {
        state.project.vehicles = [...vehicles];
    },

    /**
     * Set vehicle
     *
     * @param {*} state
     * @returns
     */
    [SET_VEHICLE](state, vehicle) {
        state.project.vehicle = vehicle;
    },

    /**
     * Add vehicle
     *
     * @param {*} state
     * @param {Number} vehicle to append to vehicles list
     */
    [ADD_VEHICLE](state, vehicle) {
        state.project.vehicles = [
            ...(state.project.vehicles ? state.project.vehicles : []),
            vehicle,
        ];
    },

    /**
     * Update vehicle
     *
     * @param {*} context
     * @param {Number} params vehicle id
     */
    [UPDATE_VEHICLE](state, vehicle) {
        state.project.vehicles = state.project.vehicles.map((o) =>
            o.id == vehicle.id ? { ...o, ...vehicle } : o
        );
    },

    /**
     * Remove vehicle
     *
     * @param {*} context
     * @param {Number} params vehicle id
     */
    [REMOVE_VEHICLE](state, vehicle) {
        state.project.vehicles = (
            state.project.vehicles ? state.project.vehicles : []
        ).filter((o) => o.id != vehicle.id);
        state.projects = state.projects.map((o) =>
            o.id == state.project.id ? { ...o, ...state.project } : o
        );
    },
};

/**
 * vehicle Store Getters
 */
const getters = {
    /**
     * Get list of vehicles
     *
     * @param {*} state
     * @returns
     */
    vehicles: (state) =>
        state.project && state.project.vehicles ? state.project.vehicles : [],

    /**
     * Get vehicle
     *
     * @param {*} state
     * @returns
     */
    vehicle: (state) =>
        state.project && state.project.vehicle ? state.project.vehicle : null,
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
