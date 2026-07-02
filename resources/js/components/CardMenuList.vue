<template>
    <div class="hc-card-menu-list">
        <div>
            <slot></slot>
        </div>
    </div>
</template>

<style>
.hc-card-menu-list {
    display: flex;
    flex-direction: row;
    border-top: 1px solid #dddddd;
    width: 100%;
    background-color: white;
    min-height: 46px;
    justify-content: center;
    z-index: 3;
    position: relative;
}

.hc-card-menu-list > div {
    display: flex;
    flex-direction: row;
    max-width: 100%;
    height: 100%;
    padding: 3px;
    gap: 2px;
    overflow-x: visible;
    overflow-y: auto;
}

.hc-card-menu-list > div > * {
    width: 40px;
    min-width: 40px;
}

.hc-card-menu-list > div::-webkit-scrollbar {
    display: none;
}

.dark .hc-card-menu-list {
    border-top-color: #404040;
    background-color: #272827;
}

@media (min-width: 1200px) {
    .hc-card-menu-list > div {
        overflow-y: visible;
    }

    .hc-card-menu-list > div > * {
        position: relative;
    }
}
</style>
