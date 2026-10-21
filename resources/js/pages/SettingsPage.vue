<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-heading font-bold text-foreground">{{ t('settings') }}</h1>
      <p class="text-muted-foreground text-sm mt-1">{{ t('manageSettings') }}</p>
    </div>

    <!-- PWA Install Card -->
    <InstallAppCard />

    <!-- Settings Tabs -->
    <div class="space-y-4">
      <div class="flex border-b border-border gap-4 overflow-x-auto max-w-full">
        <button
          id="tab-profile"
          type="button"
          @click="activeTab = 'profile'"
          :class="[
            'pb-3 text-sm font-semibold transition-colors flex items-center gap-2 relative cursor-pointer',
            activeTab === 'profile'
              ? 'text-primary border-b-2 border-primary'
              : 'text-muted-foreground hover:text-foreground'
          ]"
        >
          <UserCog class="h-4 w-4" /> {{ t('profile') }}
        </button>
        <button
          id="tab-security"
          type="button"
          @click="activeTab = 'security'"
          :class="[
            'pb-3 text-sm font-semibold transition-colors flex items-center gap-2 relative cursor-pointer',
            activeTab === 'security'
              ? 'text-primary border-b-2 border-primary'
              : 'text-muted-foreground hover:text-foreground'
          ]"
        >
          <KeyRound class="h-4 w-4" /> {{ t('security') }}
        </button>
        <button
          type="button"
          @click="activeTab = 'access'"
          :class="[
            'pb-3 text-sm font-semibold transition-colors flex items-center gap-2 relative',
            activeTab === 'access'
              ? 'text-primary border-b-2 border-primary'
              : 'text-muted-foreground hover:text-foreground'
          ]"
        >
          <Shield class="h-4 w-4" /> {{ t('access') }}
        </button>
      </div>

      <!-- Tab: Profile -->
      <div v-if="activeTab === 'profile'" class="rounded-xl border border-border bg-card p-6 shadow-xs max-w-xl space-y-4">
        <div>
          <h2 class="text-base font-semibold text-foreground">{{ t('profileInfo') }}</h2>
          <p class="text-xs text-muted-foreground">{{ t('updateDisplayName') }}</p>
        </div>

        <form @submit.prevent="handleSaveProfile" class="space-y-4">
          <div>
            <label class="block text-xs font-medium text-foreground mb-1">{{ t('email') }}</label>
            <input
              :value="authState.user?.email || ''"
              disabled
              class="w-full h-9 px-3 text-xs rounded-lg border border-input bg-muted text-muted-foreground cursor-not-allowed"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-foreground mb-1">{{ t('displayName') }}</label>
            <input
              v-model="displayName"
              type="text"
              placeholder="Your name..."
              class="w-full h-9 px-3 text-xs rounded-lg border border-input bg-background focus:outline-none focus:ring-1 focus:ring-ring"
            />
          </div>

          <button
            type="submit"
            :disabled="profileSaving"
            class="h-9 px-4 inline-flex items-center gap-1.5 rounded-lg bg-primary text-primary-foreground text-xs font-medium hover:bg-primary/90 transition-colors disabled:opacity-50"
          >
            <Loader2 v-if="profileSaving" class="h-3.5 w-3.5 animate-spin" />
            {{ t('saveChanges') }}
          </button>
        </form>
      </div>

      <!-- Tab: Security -->
      <div v-if="activeTab === 'security'" class="space-y-4">
        <ChangePasswordForm :show-current-password="false" />
      </div>

      <!-- Tab: Access & User Roles -->
      <div v-if="activeTab === 'access'" class="space-y-6">
        <!-- Invite User Card -->
        <div class="rounded-xl border border-border bg-card p-6 shadow-xs space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-base font-semibold text-foreground">{{ t('userManagement') }}</h2>
              <p class="text-xs text-muted-foreground">{{ t('manageStaffRolesDesc') }}</p>
            </div>
          </div>

          <form @submit.prevent="handleInviteUser" class="flex flex-col sm:flex-row gap-3">
            <input
              v-model="inviteEmail"
              type="email"
              placeholder="user@example.com"
              required
              class="flex-1 h-9 px-3 text-xs rounded-lg border border-input bg-background focus:outline-none focus:ring-1 focus:ring-ring"
            />
            <select
              v-model="inviteRole"
              class="h-9 px-3 text-xs rounded-lg border border-input bg-background focus:outline-none focus:ring-1 focus:ring-ring font-medium"
            >
              <option value="admin">Admin</option>
              <option value="editor">Editor</option>
              <option value="viewer">Viewer</option>
            </select>
            <button
              type="submit"
              :disabled="inviting"
              class="h-9 px-4 inline-flex items-center justify-center gap-1.5 rounded-lg bg-primary text-primary-foreground text-xs font-semibold hover:bg-primary/90 transition-colors disabled:opacity-50 cursor-pointer"
            >
              <Loader2 v-if="inviting" class="h-3.5 w-3.5 animate-spin" />
              <UserPlus v-else class="h-3.5 w-3.5" />
              {{ t('addUser') }}
            </button>
          </form>
        </div>

        <!-- Users Table -->
        <div class="rounded-xl border border-border bg-card shadow-xs overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
              <thead class="bg-muted/50 text-muted-foreground uppercase text-[11px] font-semibold border-b border-border">
                <tr>
                  <th class="px-4 py-3">{{ t('email') }}</th>
                  <th class="px-4 py-3">{{ t('role') }}</th>
                  <th class="px-4 py-3">{{ t('joined') }}</th>
                  <th class="px-4 py-3 text-right">{{ t('actions') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-border/60">
                <tr v-if="usersLoading">
                  <td colspan="4" class="text-center py-8">
                    <Loader2 class="h-6 w-6 animate-spin text-primary mx-auto" />
                  </td>
                </tr>
                <tr v-else-if="users.length === 0">
                  <td colspan="4" class="text-center py-6 text-muted-foreground">
                    {{ t('noUsersFound') }}
                  </td>
                </tr>
                <tr v-for="u in users" :key="u.id" class="hover:bg-muted/30">
                  <td class="px-4 py-3 font-medium text-foreground">{{ u.email }}</td>
                  <td class="px-4 py-3">
                    <select
                      :value="u.role"
                      @change="(e) => handleUpdateRole(u.id, e.target.value)"
                      :disabled="u.email === 'emdart.dev2000@gmail.com'"
                      class="h-7 px-2 text-xs rounded border border-input bg-background font-medium capitalize"
                    >
                      <option value="admin">Admin</option>
                      <option value="editor">Editor</option>
                      <option value="viewer">Viewer</option>
                    </select>
                  </td>
                  <td class="px-4 py-3 text-muted-foreground">{{ (u.created_at || '').split('T')[0] }}</td>
                  <td class="px-4 py-3 text-right">
                    <button
                      v-if="u.email !== 'emdart.dev2000@gmail.com' && u.id !== authState.user?.id"
                      type="button"
                      @click="handleDeleteUser(u.id)"
                      class="h-7 w-7 inline-flex items-center justify-center rounded hover:bg-destructive/10 text-muted-foreground hover:text-destructive cursor-pointer"
                      title="Delete User"
                    >
                      <Trash2 class="h-3.5 w-3.5" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { UserCog, KeyRound, Shield, Loader2, UserPlus, Trash2 } from 'lucide-vue-next';
import { t } from '@/lib/language';
import { authState } from '@/lib/auth';
import InstallAppCard from '@/components/InstallAppCard.vue';
import ChangePasswordForm from '@/components/ChangePasswordForm.vue';
import api from '@/lib/api';

const route = useRoute();
const activeTab = ref(route.query.tab || 'profile');

watch(
  () => route.query.tab,
  (tab) => {
    if (tab) activeTab.value = tab;
  }
);

// Profile state
const displayName = ref(authState.user?.name || '');
const profileSaving = ref(false);

// Access state
const users = ref([]);
const usersLoading = ref(true);
const inviteEmail = ref('');
const inviteRole = ref('viewer');
const inviting = ref(false);

async function handleSaveProfile() {
  profileSaving.value = true;
  try {
    const res = await api.put('/auth/profile', { name: displayName.value });
    if (res.data.data) {
      authState.user = res.data.data;
    }
  } catch (err) {
    console.error('Failed to update profile', err);
  } finally {
    profileSaving.value = false;
  }
}

async function fetchUsers() {
  usersLoading.value = true;
  try {
    const res = await api.get('/users');
    const raw = res.data?.data || res.data?.users || res.data;
    users.value = Array.isArray(raw) ? raw : [];
  } catch (err) {
    console.error('Failed to load users', err);
    users.value = [];
  } finally {
    usersLoading.value = false;
  }
}

watch(
  () => activeTab.value,
  (newTab) => {
    if (newTab === 'access') {
      fetchUsers();
    }
  }
);

async function handleInviteUser() {
  if (!inviteEmail.value) return;
  inviting.value = true;
  try {
    await api.post('/users', {
      email: inviteEmail.value,
      role: inviteRole.value,
    });
    inviteEmail.value = '';
    await fetchUsers();
  } catch (err) {
    console.error('Failed to invite user', err);
  } finally {
    inviting.value = false;
  }
}

async function handleUpdateRole(id, role) {
  try {
    await api.put(`/users/${id}/role`, { role });
    await fetchUsers();
  } catch (err) {
    console.error('Failed to update role', err);
  }
}

async function handleDeleteUser(id) {
  try {
    await api.delete(`/users/${id}`);
    await fetchUsers();
  } catch (err) {
    console.error('Failed to delete user', err);
  }
}

onMounted(() => {
  fetchUsers();
});
</script>
