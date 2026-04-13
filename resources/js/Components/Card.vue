<script setup lang="ts">
const props = defineProps<{
    logo?: string;
    overline?: string;
    title: string;
    content: string;
    image: string;
}>();
</script>
<template>
    <div class="c-card">
        <div class="c-card__inner">
            <div class="c-card__wrapper-image">
                <img :src="props.image" alt="" class="c-card__image" />
            </div>
            <div class="c-card__wrapper-content">
                <h2 class="c-card__title">{{ props.title }}</h2>
                <p class="c-card__overline">{{ props.overline }}</p>
                <p class="c-card__content">{{ props.content }}</p>
            </div>
        </div>
    </div>
</template>
<style lang="scss" scoped>
.c-card {
    &__inner {
        border-radius: 8px;
        background-color: var(--color-white);
        overflow: hidden;
    }
    &__title {
        font-size: 1.5rem;
        margin-bottom: 10px;
        color: var(--color-gray);
    }
    &__overline {
        font-size: 0.875rem;
        color: var(--color-gray-dark);
    }
    &__content {
        font-size: 1rem;
        color: var(--color-gray-dark);
        margin-bottom: 10px;
    }
    &__wrapper-image {
        width: 100%;
        height: 200px;
        overflow: hidden;
    }
    &__image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scale(1.01);
        transition: all 0.3s ease;

        &:hover {
            transform: scale(1.08);
        }
    }
    &__wrapper-content {
        padding: 20px;
        background-color: var(--color-black);
    }
}
</style>
