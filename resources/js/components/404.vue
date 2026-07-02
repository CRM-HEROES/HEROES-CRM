<template>
    <div class="not-found">
        <h1>
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="120"
                height="120"
                fill="none"
                viewBox="0 0 513 513"
                id="sad"
            >
                <path
                    fill="#FFE17D"
                    d="M256.609 512.896C397.994 512.896 512.609 398.281 512.609 256.896C512.609 115.511 397.994 0.895874 256.609 0.895874C115.224 0.895874 0.609375 115.511 0.609375 256.896C0.609375 398.281 115.224 512.896 256.609 512.896Z"
                ></path>
                <path
                    fill="#7D5046"
                    d="M256.609 329.153C221.085 329.153 187.375 342.75 166.432 365.52 162.569 369.714 162.843 376.246 167.037 380.105 171.231 383.964 177.771 383.694 181.626 379.496 198.73 360.898 226.762 349.798 256.609 349.798 286.456 349.798 314.488 360.899 331.593 379.496 335.448 383.694 341.988 383.964 346.182 380.105 350.376 376.246 350.65 369.714 346.787 365.52 325.843 342.749 292.133 329.153 256.609 329.153zM169.899 256.896C158.497 256.896 149.254 247.653 149.254 236.251V219.735C149.254 208.333 158.497 199.09 169.899 199.09 181.301 199.09 190.544 208.333 190.544 219.735V236.251C190.544 247.653 181.301 256.896 169.899 256.896zM343.319 256.896C331.917 256.896 322.674 247.653 322.674 236.251V219.735C322.674 208.333 331.917 199.09 343.319 199.09 354.721 199.09 363.964 208.333 363.964 219.735V236.251C363.964 247.653 354.721 256.896 343.319 256.896z"
                ></path>
            </svg>
        </h1>
        <p>
            La page que vous cherchez n'existe pas<br />
            ou vous n'avez pas les autorisations nécessaires<br />
            pour y accéder.
        </p>
        <router-link
            to="/"
            style="
                display: inline-block;
                height: 48px;
                line-height: 48px;
                background-color: #4784d5;
                color: white;
                padding: 0 16px;
                border-radius: 1000px;
                text-decoration: none;
            "
            >Revenir au Dashboard</router-link
        >
    </div>
</template>

<script>
export default {
    name: "NotFound",
};
</script>

<style scoped>
.not-found {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 32px;
    width: 100%;
    height: 100%;
}

h1 {
    font-size: 6rem;
    color: #ff6b6b;
}

p {
    font-size: 1.5rem;
    margin: 20px 0;
    text-align: center;
}
</style>
