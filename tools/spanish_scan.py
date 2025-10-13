import json
import re
import sys
from pathlib import Path

ROOT = Path('.')
TARGET_DIRS = [ROOT / 'app', ROOT / 'database']
SPANISH_CHARS = set('áéíóúñÁÉÍÓÚÑ¡¿')
SPANISH_WORDS = {
    'configuracion', 'configuraciones', 'auditoria', 'auditorias', 'usuario', 'usuarios',
    'reporte', 'reportes', 'mensaje', 'mensajes', 'bloqueado', 'bloqueada', 'bloqueados',
    'bloqueadas', 'habilitado', 'habilitada', 'habilitados', 'habilitadas', 'deshabilitado',
    'deshabilitada', 'deshabilitados', 'deshabilitadas', 'tema', 'temas', 'personalizado',
    'personalizada', 'personalizados', 'personalizadas', 'guardado', 'guardada', 'eliminado',
    'eliminada', 'existente', 'existe', 'actualizado', 'actualizada', 'actualizar', 'error',
    'errores', 'exito', 'inicio', 'sesion', 'contraseña', 'contraseñas', 'credenciales', 'correo',
    'precalent', 'prewarm', 'temperatura', 'reiniciar', 'detener', 'reanudar', 'capacidad',
    'capacidades', 'tema', 'colores', 'preferencia', 'volver', 'encontrado', 'preloader',
    'predeterminado', 'predeterminada', 'predeterminados', 'predeterminadas', 'gracia', 'gracias',
    'minutos', 'horas', 'reintentar', 'fallo', 'desconocido', 'sesion', 'sesiones', 'detalles',
}

WORD_RE = re.compile(r"[A-Za-zÀ-ÿ_][A-Za-zÀ-ÿ0-9_]*", re.UNICODE)

results = {}

for target in TARGET_DIRS:
    if not target.exists():
        continue
    for path in target.rglob('*.php'):
        try:
            text = path.read_text(encoding='utf-8')
        except UnicodeDecodeError:
            text = path.read_text(encoding='latin-1')
        words = set()
        for match in WORD_RE.finditer(text):
            word = match.group(0)
            lower = word.lower()
            if any(ch in SPANISH_CHARS for ch in word):
                words.add(word)
            elif lower in SPANISH_WORDS:
                words.add(word)
            elif any(key in lower for key in ('configuracion', 'auditoria', 'usuario', 'reporte', 'capacidad', 'tema', 'mensaje')):
                words.add(word)
        string_hits = []
        for m in re.finditer(r"['\"]([^'\"]*[áéíóúñÁÉÍÓÚÑ][^'\"]*)['\"]", text):
            snippet = m.group(0)
            if len(snippet) > 120:
                snippet = snippet[:117] + '...' + snippet[-1]
            string_hits.append(snippet)
        if words or string_hits:
            results[str(path)] = {
                'identifiers': sorted(words)[:20],
                'string_samples': string_hits[:5],
            }

output = json.dumps(results, indent=2, ensure_ascii=False)
sys.stdout.buffer.write(output.encode('utf-8'))
sys.stdout.buffer.write(b'\n')
