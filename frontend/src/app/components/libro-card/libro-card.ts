import { Component, input, signal } from '@angular/core';
import { Libro } from '../../interfaces/libro';

@Component({
  imports: [],
  selector: 'app-libro-card',
  styleUrl: './libro-card.css',
  templateUrl: './libro-card.html',
})
export class LibroCard {
  libro = input.required<Libro>();
  protected readonly portadaRota = signal(false);
}
