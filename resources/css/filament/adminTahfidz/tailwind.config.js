import preset from "../../../../vendor/filament/filament/tailwind.config.preset";

export default {
    presets: [preset],
    content: [
        "./app/Filament/AdminTahfidz/**/*.php",
        "./resources/views/filament/admin-tahfidz/**/*.blade.php",
        // "./resources/views/filament/tahfidz/**/*.blade.php",
        "./vendor/filament/**/*.blade.php",
    ],
};
