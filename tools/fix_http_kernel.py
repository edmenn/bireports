from pathlib import Path
path = Path('app/Http/Kernel.php')
text = path.read_text(encoding='utf-8', errors='ignore')
text = text.replace('    /**\n     * Middleware globales que se ejecutan en cada request (en orden).\n     * The first item wraps the entire stack and can capture early exceptions.\n     */', "    /**\n     * Global middleware executed on every request (in order).\n     * The first item wraps the entire stack and can capture early exceptions.\n     */")
text = text.replace('        // Protección básica y cabeceras', '        // Basic protection and headers')
text = text.replace('            // Auditoría existente', '            // Existing audit tracker')
text = text.replace('        // ?? NUEVO: bloquea cambio de contraseña para usuarios Entra', '        // Prevent password changes for Entra-managed users')
path.write_text(text, encoding='utf-8')
