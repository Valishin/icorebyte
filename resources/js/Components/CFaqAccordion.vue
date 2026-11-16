<script setup lang="ts">
  import { ref } from 'vue'

  defineProps<{
    items: { question: string; answer: string }[]
  }>()

  const openIndex = ref<number | null>(null)

  const toggle = (i: number) => {
    openIndex.value = openIndex.value === i ? null : i
  }
</script>

<template>
  <div class="c-faq-accordion">
    <div class="c-faq-accordion__inner o-container">
      <ul class="c-faq-accordion__list">
        <li
          v-for="(item, i) in items"
          :key="item.question"
          class="c-faq-accordion__item"
        >
          <button
            class="c-faq-accordion__trigger"
            :aria-expanded="openIndex === i"
            :aria-controls="`faq-panel-${i}`"
            @click="toggle(i)"
          >
            <span class="c-faq-accordion__question">{{ item.question }}</span>
            <span class="c-faq-accordion__icon" :class="{ 'is-open': openIndex === i }" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 9l6 6 6-6" />
              </svg>
            </span>
          </button>

          <div
            :id="`faq-panel-${i}`"
            class="c-faq-accordion__panel"
            :class="{ 'is-open': openIndex === i }"
            role="region"
          >
            <p class="c-faq-accordion__answer">{{ item.answer }}</p>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .c-faq-accordion {
    padding-bottom: 80px;

    &__list {
      list-style: none;
      padding: 0;
      margin: 0;
      max-width: 760px;
      margin-inline: auto;
      display: flex;
      flex-direction: column;
      gap: 0;
    }

    &__item {
      border-bottom: 1px solid var(--color-border-subtle);

      &:first-child {
        border-top: 1px solid var(--color-border-subtle);
      }
    }

    &__trigger {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 20px 0;
      background: none;
      border: none;
      cursor: pointer;
      text-align: left;

      &:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 2px;
        border-radius: 4px;
      }
    }

    &__question {
      font-size: 1rem;
      font-weight: 600;
      color: var(--color-gray);
      line-height: 1.4;
    }

    &__icon {
      flex-shrink: 0;
      width: 20px;
      height: 20px;
      color: var(--color-primary);
      transition: transform 0.25s ease;

      svg { width: 100%; height: 100%; }

      &.is-open {
        transform: rotate(180deg);
      }
    }

    &__panel {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease, padding 0.3s ease;

      &.is-open {
        max-height: 400px;
        padding-bottom: 20px;
      }
    }

    &__answer {
      font-size: 0.925rem;
      color: var(--color-gray-dark);
      line-height: 1.7;
      margin: 0;
    }
  }
</style>
