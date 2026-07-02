<template>
    <div :class="['stat-chart-template', selected ? 'selected' : '']">
        <div class="stat-chart-template-content">
            <div class="stat-chart-template-icon">
                <i :class="chart.icon"></i>
            </div>
            <div class="stat-chart-template-body">
                <span
                    class="stat-chart-template-title"
                    v-text="chart.name"
                ></span>
                <p
                    class="stat-chart-template-description"
                    v-text="chart.description"
                ></p>
            </div>
        </div>
    </div>
</template>

<style>
.stat-chart-template {
    width: 33.333%;
    padding: 6px;
}

.stat-chart-template-content {
    background-color: white;
    border-radius: 8px;
    display: flex;
    flex-direction: row;
    gap: 20px;
    padding: 12px;
    border: 1px solid #eee;
    height: 100%;
    cursor: pointer;
}

.selected > .stat-chart-template-content {
    border: 1px solid #6805b5;
}

.stat-chart-template-content:hover {
    background-color: #fafafa;
}

.stat-chart-template-icon {
    font-size: 60px;
    line-height: 50px;
}

.stat-chart-template-icon > i {
    vertical-align: top;
}

.stat-chart-template-body {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.stat-chart-template-title {
    font-size: 16px;
    font-weight: bold;
}

.stat-chart-template-description {
    font-size: 12px;
    color: #555555;
    margin-bottom: 0;
}

@media (max-width: 1000px) {
    .stat-chart-template {
        width: 50%;
    }
}

@media (max-width: 767px) {
    .stat-chart-template {
        width: 100%;
    }
}
</style>

<script>
export default {
    props: {
        /**
         *
         */
        chart: Object,

        /**
         *
         */
        selected: Boolean,
    },
};
</script>
