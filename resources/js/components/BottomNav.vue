<template>

    <nav
        class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-gray-200 shadow-lg flex justify-around py-3"
    >

        <RouterLink
            to="/dashboard"
            class="nav-item"
            :class="{ active: $route.path === '/dashboard' }"
        >
            <span class="material-symbols-outlined">
                home
            </span>

            <small>Inicio</small>
        </RouterLink>

        <RouterLink
            to="/notificaciones"
            class="nav-item"
            :class="{ active: $route.path === '/notificaciones' }"
        >
            <span class="material-symbols-outlined">
                notifications
            </span>

            <small>Alertas</small>
        </RouterLink>

        <RouterLink
            to="/reportes"
            class="nav-item"
            :class="{ active: $route.path === '/reportes' }"
        >
            <span class="material-symbols-outlined">
                bar_chart
            </span>

            <small>Informes</small>
        </RouterLink>

        <RouterLink
            to="/perfil"
            class="nav-item"
            :class="{ active: $route.path === '/perfil' }"
        >
            <span class="material-symbols-outlined">
                person
            </span>

            <small>Perfil</small>
        </RouterLink>

    </nav>

</template>

<style scoped>
.nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;

    color: #6b7280;

    text-decoration: none;

    padding: 8px 12px;

    border-radius: 16px;

    transition: all .2s;
}

.active {
    background: #2ecc71;
    color: #005027;
}

small {
    font-size: 10px;
}
</style>