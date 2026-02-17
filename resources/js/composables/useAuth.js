import { reactive, readonly } from 'vue';
import api from '../api.js';

const state = reactive({
    user: null,
    checked: false,
});

export function useAuth() {
    async function checkAuth() {
        if (state.checked) return;
        try {
            const res = await api.getUser();
            state.user = res.data.user;
        } catch {
            state.user = null;
        }
        state.checked = true;
    }

    async function login(email, password) {
        const res = await api.login({ email, password });
        state.user = res.data.user;
        state.checked = true;
        return res.data.user;
    }

    async function logout() {
        await api.logout();
        state.user = null;
    }

    function isAdmin() {
        return state.user?.is_admin === true;
    }

    function isAuthenticated() {
        return state.user !== null;
    }

    return {
        state: readonly(state),
        checkAuth,
        login,
        logout,
        isAdmin,
        isAuthenticated,
    };
}
