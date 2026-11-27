export function formatearValoracion(media: number | null): string {
    return media ? media.toFixed(1).replace('.', ',') : '—';
}

export function estrellas(puntuacion: number): string {
    const llenas = Math.max(0, Math.min(5, Math.round(puntuacion)));
    return '★'.repeat(llenas) + '☆'.repeat(5 - llenas);
}