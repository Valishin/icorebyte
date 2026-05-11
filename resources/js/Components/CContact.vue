<script setup lang="ts">
  import { useInView } from '@/composables/useInView'
  import { useForm, usePage } from '@inertiajs/vue3'
  import CButton from './CButton.vue'
  import CPlaceCard from './CPlaceCard.vue'
  import CTitle from './CTitle.vue'

  const { el: colsRef, isVisible: colsVisible } = useInView(0.1)

  const page = usePage<{
    flash: { success?: string }
  }>()

  const services = [
    'Reparación de ordenadores',
    'Reparación de tablets',
    'Reparación de móviles',
    'Reparación de impresoras',
    'Mantenimiento IT empresa',
    'Servicio a domicilio',
    'Electrónica en placa',
    'Página web',
    'Dashboard empresarial',
    'Otros'
  ]

  const contactForm = useForm({
    name: '',
    email: '',
    phone: '',
    service: '',
    message: ''
  })

  const sendContact = () => {
    contactForm.post(route('contact.send'), {
      preserveScroll: true,
      onSuccess: () => contactForm.reset()
    })
  }
</script>

<template>
  <div class="c-contact">
    <div class="c-contact__inner">
      <div class="c-contact__container o-container">
        <div class="o-col-12@md o-col-8@sm o-col-4@xs">
          <CTitle
            :title="'¿Hablamos de tu proyecto?'"
            :description="'Cuéntanos qué necesitas y te enviaremos un presupuesto sin compromiso en menos de 24 horas.'"
            :color="'secondary'"
          />
        </div>
      </div>
      <div
        ref="colsRef"
        class="c-contact__container o-container"
        :class="{ 'is-visible': colsVisible }"
      >
        <div class="c-contact__col c-contact__col--cards o-col-4@md o-col-6@sm o-col-4@xs">
          <CPlaceCard
            :icon="'IconPhone'"
            :title="'+34 678 758 210'"
            :description="'Contáctanos por teléfono o WhatsApp'"
            :iconColor="'primary'"
            :iconSize="'24px'"
            :is-wrapper="true"
          />
          <CPlaceCard
            :icon="'IconEmail'"
            :title="'info@icorebyte.com'"
            :description="'Respuesta en menos de 24 horas'"
            :iconColor="'primary'"
            :iconSize="'24px'"
            :is-wrapper="true"
          />
          <CPlaceCard
            :icon="'IconLocation'"
            :title="'Ubicación'"
            :description="'Carretera de Palamós 57 local, Sant Feliu de Guixols, Girona, 17220'"
            :iconColor="'primary'"
            :iconSize="'24px'"
            :is-wrapper="true"
          />
          <CPlaceCard
            :icon="'IconTime'"
            :title="'Horario'"
            :description="'Lunes a Viernes: 10:00 - 13:30 y 17:00 - 20:00'"
            :iconColor="'primary'"
            :iconSize="'24px'"
            :is-wrapper="true"
          />
        </div>

        <div class="c-contact__col o-col-8@md o-col-6@sm o-col-4@xs">
          <div class="c-contact__form-wrapper">
            <form @submit.prevent="sendContact" novalidate class="c-contact__form">
              <!-- Fila 1: Nombre + Email -->
              <div class="c-contact__row">
                <div class="c-contact__field">
                  <label class="c-contact__label">Nombre</label>
                  <input
                    v-model="contactForm.name"
                    type="text"
                    placeholder="Tu nombre"
                    class="c-contact__input"
                  />
                  <p v-if="contactForm.errors.name" class="c-contact__error">
                    {{ contactForm.errors.name }}
                  </p>
                </div>
                <div class="c-contact__field">
                  <label class="c-contact__label">Email</label>
                  <input
                    v-model="contactForm.email"
                    type="email"
                    placeholder="tu@email.com"
                    class="c-contact__input"
                  />
                  <p v-if="contactForm.errors.email" class="c-contact__error">
                    {{ contactForm.errors.email }}
                  </p>
                </div>
              </div>

              <!-- Fila 2: Teléfono + Servicio -->
              <div class="c-contact__row">
                <div class="c-contact__field">
                  <label class="c-contact__label">Teléfono</label>
                  <input
                    v-model="contactForm.phone"
                    type="tel"
                    placeholder="+34 XXX XXX XXX"
                    class="c-contact__input"
                  />
                  <p v-if="contactForm.errors.phone" class="c-contact__error">
                    {{ contactForm.errors.phone }}
                  </p>
                </div>
                <div class="c-contact__field">
                  <label class="c-contact__label">Servicio</label>
                  <div class="c-contact__select-wrapper">
                    <select
                      v-model="contactForm.service"
                      class="c-contact__input c-contact__input--select"
                      :class="{ 'c-contact__input--placeholder': !contactForm.service }"
                    >
                      <option value="" disabled>Selecciona un servicio</option>
                      <option v-for="service in services" :key="service" :value="service">
                        {{ service }}
                      </option>
                    </select>
                    <svg
                      class="c-contact__select-arrow"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 9l-7 7-7-7"
                      />
                    </svg>
                  </div>
                  <p v-if="contactForm.errors.service" class="c-contact__error">
                    {{ contactForm.errors.service }}
                  </p>
                </div>
              </div>

              <!-- Mensaje -->
              <div class="c-contact__field">
                <label class="c-contact__label">Mensaje</label>
                <textarea
                  v-model="contactForm.message"
                  placeholder="Describe tu proyecto o necesidad..."
                  rows="6"
                  class="c-contact__input c-contact__input--textarea"
                ></textarea>
                <p v-if="contactForm.errors.message" class="c-contact__error">
                  {{ contactForm.errors.message }}
                </p>
              </div>

              <!-- Botón -->
              <CButton
                title="Enviar Mensaje"
                native-type="submit"
                color="gradient"
                icon="IconEmail"
                :loading="contactForm.processing"
                :disabled="contactForm.processing"
                class="c-contact__submit"
              />
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
  .c-contact {
    &__inner {
      padding: 4rem 0;
    }

    &__container {
      // ── Animación columnas ───────────────────────────────
      .c-contact__col--cards {
        opacity: 0;
        transform: translateX(-24px);
        transition: opacity 0.55s ease, transform 0.55s ease;

        @media (prefers-reduced-motion: reduce) { opacity: 1; transform: none; }
      }

      .c-contact__col:not(.c-contact__col--cards) {
        opacity: 0;
        transform: translateX(24px);
        transition: opacity 0.55s ease 0.15s, transform 0.55s ease 0.15s;

        @media (prefers-reduced-motion: reduce) { opacity: 1; transform: none; }
      }

      &.is-visible {
        .c-contact__col--cards                    { opacity: 1; transform: none; }
        .c-contact__col:not(.c-contact__col--cards) { opacity: 1; transform: none; }
      }

      // En mobile ambos vienen desde abajo
      @media (max-width: 1023px) {
        .c-contact__col--cards,
        .c-contact__col:not(.c-contact__col--cards) {
          transform: translateY(20px);
        }
      }

      display: flex;
      flex-direction: column-reverse;
      align-items: center;
      gap: 2rem;

      @include from-sm {
        flex-direction: column;
      }

      @include from-md {
        flex-direction: row;
      }
    }

    &__col {
      &--cards {
        display: flex;
        flex-direction: column;
        justify-content: space-evenly;
        gap: 1.5rem;
      }
    }

    &__form-wrapper {
      background: var(--color-primary-dark);
      border: 1px solid var(--color-gray-dark);
      border-radius: 1rem;
      padding: 2rem;

      .theme-light & {
        border: 0px solid transparent;
      }
    }

    &__flash {
      margin-bottom: 1.5rem;
      background: rgba(0, 107, 223, 0.1);
      border: 1px solid rgba(0, 107, 223, 0.3);
      color: var(--color-primary);
      padding: 0.75rem 1rem;
      border-radius: 0.75rem;
      font-size: 0.875rem;
    }

    &__form {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    &__row {
      display: grid;
      grid-template-columns: 1fr;
      gap: 1rem;

      @include from-sm {
        grid-template-columns: 1fr 1fr;
      }
    }

    &__field {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    &__label {
      font-size: 0.75rem;
      font-weight: 600;
      color: var(--color-gray);
      text-transform: uppercase;
      letter-spacing: 0.1em;
    }

    &__input {
      width: 100%;
      background: var(--color-black);
      border: 1px solid var(--color-gray-dark);
      border-radius: 0.75rem;
      padding: 0.75rem 1rem;
      color: var(--color-white);
      font-size: 1rem;
      transition: border-color 0.2s;

      &::placeholder {
        color: var(--color-gray-dark);
      }

      &:focus {
        outline: none;
        border-color: var(--color-primary);
      }

      &--textarea {
        resize: none;
      }

      &--select {
        appearance: none;
        cursor: pointer;
      }

      &--placeholder {
        color: var(--color-gray-dark);
      }

      option {
        background: var(--color-black);
        color: var(--color-white);
      }
    }

    &__select-wrapper {
      position: relative;
    }

    &__select-arrow {
      position: absolute;
      right: 1rem;
      top: 50%;
      transform: translateY(-50%);
      width: 1rem;
      height: 1rem;
      color: var(--color-gray-dark);
      pointer-events: none;
    }

    &__error {
      color: #f87171;
      font-size: 0.75rem;
      margin: 0;
    }

    &__submit {
      width: 100%;
      justify-content: center;
      margin-top: 0.5rem;
      border-radius: 0.75rem !important;
      padding: 1rem !important;
      font-weight: 600;
    }
  }
</style>
