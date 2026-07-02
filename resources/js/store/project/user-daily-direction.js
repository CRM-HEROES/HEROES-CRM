import UserDailyDirectionService from "@/apis/project/user/daily-direction";

import {
    SHOW_USER_DAILY_DIRECTION,
    TOGGLE_USER_DAILY_DIRECTION,
    INIT_USER_DAILY_DIRECTIONS,
} from "@/actions/project/user/daily-direction";

/**
 * UserDocument Store state
 */
export const state = {
    dailyDirections: [],
};

/**
 * UserDocument Store Actions
 */
export const actions = {};

/**
 * User document Store Mutations
 */
export const mutations = {
    /**
     * Set user documents
     *
     * @param {*} state
     * @returns
     */
    [TOGGLE_USER_DAILY_DIRECTION](state, dailyDirection) {
        if (
            !state.project.dailyDirections ||
            !Array.isArray(state.project.dailyDirections)
        ) {
            state.project.dailyDirections = [];
        }

        if (
            state.project.dailyDirections.find(
                (dd) =>
                    dd.user_id == dailyDirection.user_id &&
                    dd.direction_at == dailyDirection.direction_at
            )
        ) {
            state.project.dailyDirections =
                state.project.dailyDirections.filter(
                    (dd) =>
                        !(
                            dd.user_id == dailyDirection.user_id &&
                            dd.direction_at == dailyDirection.direction_at
                        )
                );
        } else {
            /*const sum = parseInt(Math.random() * 40 + 230);
            const r = parseInt(Math.random() * Math.min(sum, 255));
            const g = parseInt(Math.random() * Math.min(sum - r, 255));
            const b = sum - r - g;*/
            const colors = [
                "#c90a0a",
                "#ed8d11",
                "#c9b30b",
                "#1ea707",
                "#008fb1",
                "#6611d3",
                "#b908a1",
            ];

            state.project.dailyDirections = [
                ...state.project.dailyDirections,
                {
                    ...dailyDirection,
                    color: colors[
                        new Date(dailyDirection.direction_at).getDay()
                    ],
                    /*"#" +
                        ((1 << 24) + (r << 16) + (g << 8) + b)
                            .toString(16)
                            .slice(1),*/
                },
            ];
        }
    },

    /**
     * Set user documents
     *
     * @param {*} state
     * @returns
     */
    [INIT_USER_DAILY_DIRECTIONS](state) {
        state.project.dailyDirections = [];
    },
};

/**
 * User document Store Getters
 */
export const getters = {
    /**
     * Get list of user documents
     *
     * @param {*} state
     * @returns
     */
    dailyDirections: (state) =>
        state.project &&
        state.project.dailyDirections &&
        Array.isArray(state.project.dailyDirections)
            ? state.project.dailyDirections
            : [],

    /**
     *
     *
     * @param {*} state
     * @returns
     */
    directionFor: (state) => (user, date) =>
        state.project &&
        state.project.dailyDirections &&
        Array.isArray(state.project.dailyDirections)
            ? state.project.dailyDirections.find(
                  (dd) => dd.user_id == user && dd.direction_at == date
              )
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
