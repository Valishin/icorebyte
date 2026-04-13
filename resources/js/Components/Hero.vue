<script setup lang="ts">
import ImageRepair from "@assets/images/logoRepara.jpg";
import Logo from "@assets/logos/logo.svg";
import Card from "./Card.vue";
</script>
<template>
    <div class="c-hero">
        <div class="c-hero__inner">
            <div class="c-hero__container o-container">
                <div
                    class="c-hero__col o-col-8@md o-col-push-2@md o-col-8@sm o-col-4@xs"
                >
                    <div class="c-hero__overline">
                        <p class="c-hero__overline-text o-font-display-caption">
                            Servicios IT profesionales en tu zona
                        </p>
                    </div>
                    <div class="c-hero__title">
                        <h1 class="c-hero__title">
                            <span class="sr-only">iCoreByte</span>
                            <component class="c-hero__svg" :is="Logo" />
                        </h1>
                    </div>
                    <div class="c-hero__subtitle">
                        <p class="c-hero__subtitle-text o-font-display-3">
                            Tu centro integral de soluciones informáticas.
                            Reparación, venta y desarrollo tecnológico al más
                            alto nivel.
                        </p>
                    </div>
                </div>
                <div class="c-hero__wrapper-cards">
                    <Card
                        :image="ImageRepair"
                        title="Reparación de equipos"
                        content="Reparamos todo tipo de dispositivos tecnológicos con rapidez y eficiencia."
                    />
                    <Card
                        :image="ImageRepair"
                        title="Reparación de equipos"
                        content="Reparamos todo tipo de dispositivos tecnológicos con rapidez y eficiencia."
                    />
                    <Card
                        :image="ImageRepair"
                        title="Reparación de equipos"
                        content="Reparamos todo tipo de dispositivos tecnológicos con rapidez y eficiencia."
                    />
                </div>
            </div>
        </div>
    </div>
</template>
<style lang="scss" scoped>
.c-hero {
    &__inner {
        height: 100vh;
        text-align: center;
        padding-top: 80px;
    }
    &__overline {
        padding-bottom: 40px;
    }
    &__overline-text {
        display: inline-block;
        color: var(--color-white);
        margin-bottom: 1rem;
        border: 1px solid var(--color-gray-dark);
        padding: 5px 10px;
        border-radius: 9999px;
    }
    &__title {
        padding-bottom: 20px;
    }
    &__title-text {
        color: var(--color-white);
        margin-bottom: 1rem;
    }

    &__subtitle-text {
        color: var(--color-gray-dark);
    }
    &__svg {
        position: relative;
        left: 50%;
        transform: translateX(-50%);
    }
    &__wrapper-cards {
        display: flex;
        gap: 20px;
        justify-content: center;
        margin-top: 40px;
    }
}
</style>
