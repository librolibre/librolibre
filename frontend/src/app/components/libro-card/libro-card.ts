import { Component, input } from '@angular/core';
import { Libro } from '../../interfaces/libro';

@Component({
  imports: [],
  selector: 'app-libro-card',
  styleUrl: './libro-card.css',
  templateUrl: './libro-card.html',
})
export class LibroCard {
  libro = input.required<Libro>();
}
