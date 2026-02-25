<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 p-4">
    <!-- Background orbs -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl"></div>
      <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-cyan-500/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-md">
      <!-- Logo -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl
                    bg-gradient-to-br from-cyan-400 to-blue-600 shadow-lg shadow-blue-500/30 mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-white">أمواج ديالى</h1>
        <p class="text-slate-400 text-sm mt-1">لوحة تحكم المشرف</p>
      </div>

      <!-- Card -->
      <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-8 shadow-2xl">
        <h2 class="text-white font-semibold text-lg mb-6">تسجيل الدخول</h2>

        <form @submit.prevent="submit" class="space-y-4">
          <!-- Email -->
          <div class="form-group">
            <label class="text-slate-300 text-sm font-medium mb-1.5 block">البريد الإلكتروني</label>
            <input
              v-model="form.email"
              type="email"
              placeholder="admin@amwaj.iq"
              autocomplete="email"
              class="w-full bg-white/10 border rounded-lg px-4 py-3 text-white placeholder-slate-500
                     focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm"
              :class="errors.email ? 'border-red-400' : 'border-white/10'"
            />
            <p v-if="errors.email" class="text-red-400 text-xs mt-1">{{ errors.email }}</p>
          </div>

          <!-- Password -->
          <div class="form-group">
            <label class="text-slate-300 text-sm font-medium mb-1.5 block">كلمة المرور</label>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPass ? 'text' : 'password'"
                placeholder="••••••••"
                autocomplete="current-password"
                class="w-full bg-white/10 border rounded-lg px-4 py-3 text-white placeholder-slate-500
                       focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm pr-4 pl-10"
                :class="errors.password ? 'border-red-400' : 'border-white/10'"
              />
              <button type="button" @click="showPass = !showPass"
                class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path v-if="!showPass" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                </svg>
              </button>
            </div>
            <p v-if="errors.password" class="text-red-400 text-xs mt-1">{{ errors.password }}</p>
          </div>

          <!-- Remember -->
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="form.remember" class="rounded border-slate-500 bg-white/10 text-blue-500" />
            <span class="text-slate-300 text-sm">تذكرني</span>
          </label>

          <!-- Submit -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full bg-gradient-to-l from-blue-600 to-cyan-500 text-white font-semibold
                   py-3 rounded-lg hover:opacity-90 transition-all shadow-lg shadow-blue-500/25
                   disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 mt-2">
            <svg v-if="form.processing" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ form.processing ? 'جاري الدخول...' : 'تسجيل الدخول' }}
          </button>
        </form>
      </div>

      <p class="text-center text-slate-600 text-xs mt-6">
        © {{ new Date().getFullYear() }} أمواج ديالى — جميع الحقوق محفوظة
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const showPass = ref(false);

const form = useForm({
  email:    '',
  password: '',
  remember: false,
});

const errors = form.errors;

function submit() {
  form.post(route('admin.login.post'), {
    onFinish: () => form.reset('password'),
  });
}
</script>
