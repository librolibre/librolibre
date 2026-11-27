import { Component, computed, input, output, signal } from '@angular/core';

@Component({
  imports: [],
  selector: 'app-modal-motivo',
  styleUrl: './modal-motivo.css',
  templateUrl: './modal-motivo.html',
  host: {
    'document:keydown.escape': 'cancelar.emit()',
  }
})
export class ModalMotivo {
  readonly titulo = input.required<string>();
  readonly texto = input('');
  readonly textoBoton = input('Confirmar');

  readonly peligro = input(false);
  readonly enviando = input(false);
  readonly error = input<string | null>(null);

  readonly confirmar = output<string>();
  readonly cancelar = output<void>();

  protected readonly maximo = 500;
  protected readonly motivo = signal('');
  protected readonly valido = computed(() => this.motivo().trim().length > 0);

  protected enviar(): void {
    if (this.valido() && !this.enviando()) {
      this.confirmar.emit(this.motivo().trim());
    }
  }
}
