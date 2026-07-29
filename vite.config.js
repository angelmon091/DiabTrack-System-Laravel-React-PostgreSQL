import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import os from 'os';

// Obtiene dinámicamente tu IP dependiendo de a qué Wi-Fi estés conectado
function getNetworkIp() {
    const interfaces = os.networkInterfaces();
    for (const name of Object.keys(interfaces)) {
        for (const iface of interfaces[name]) {
            // Buscamos una IPv4 externa (ej. tu red local)
            if (iface.family === 'IPv4' && !iface.internal) {
                return iface.address;
            }
        }
    }
    return 'localhost';
}

export default defineConfig({
    server: {
        host: '0.0.0.0', // Escuchar en todas las interfaces de red
        hmr: {
            host: getNetworkIp(), // Obtiene automáticamente la IP de la cafetería, casa u oficina
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/app.jsx',
                'resources/css/admin.css',
                'resources/css/alimentacion.css',
                'resources/css/auth-global.css',
                'resources/css/dashboard_admin.css',
                'resources/css/dashboardc.css',
                'resources/css/design-system.css',
                'resources/css/index.css',
                'resources/css/ingreso_de_datos_global.css',
                'resources/css/segunda_opcion_dashboard.css',
                'resources/css/tracking.css',
                'resources/css/visualizacion.css'
            ],
            refresh: true,
        }),
        react(),
    ],
});
