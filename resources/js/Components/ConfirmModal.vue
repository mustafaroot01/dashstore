<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="show" class="modal-backdrop" @click.self="$emit('cancel')">
        <div class="modal-box max-w-sm text-center py-8 px-6">
          <!-- Icon -->
          <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
          </div>

          <h3 class="text-lg font-bold text-slate-800 mb-1">{{ title }}</h3>
          <p class="text-slate-500 text-sm mb-6">{{ message }}</p>

          <div class="flex items-center gap-3 justify-center">
            <button @click="$emit('cancel')"
              class="btn-secondary min-w-[90px]">
              لا، إلغاء
            </button>
            <button @click="$emit('confirm')"
              class="btn-danger min-w-[90px]">
              نعم، {{ confirmLabel }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
defineProps({
  show:         { type: Boolean, default: false },
  title:        { type: String,  default: 'تأكيد الحذف' },
  message:      { type: String,  default: 'هل أنت متأكد من هذه العملية؟' },
  confirmLabel: { type: String,  default: 'حذف' },
});
defineEmits(['confirm', 'cancel']);
</script>

<style scoped>
.modal-fade-enter-active, .modal-fade-leave-active { transition: all .2s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.modal-fade-enter-from .modal-box, .modal-fade-leave-to .modal-box { transform: scale(0.95); }
</style>
