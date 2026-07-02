<template>
    <div id="hc-user-registration">
        <div id="hc-user-registration-container">
            <router-view></router-view>
        </div>
    </div>
</template>

<style>
#hc-user-registration {
    width: 100%;
    height: 100%;
    background-color: #f5f5f5;
    padding: 50px 20px;
    overflow: auto;
}

#hc-user-registration-container {
    margin: auto;
    height: auto;
    width: 400px;
    background-color: white;
    border-radius: 16px;
}
</style>
