import { Component } from '@angular/core';
import { HeaderComponent } from '../../components/header/header';
import { LibroCard } from '../../components/libro-card/libro-card';
import { Footer } from '../../components/footer/footer';

@Component({
  imports: [HeaderComponent, LibroCard, Footer],
  selector: 'app-home-component',
  styleUrl: './home-component.css',
  templateUrl: './home-component.html',
})
export class HomeComponent {
  protected readonly libros = [
    { titulo: 'Nombre del libro', genero: 'Género' },
    { titulo: 'Nombre del libro 2', genero: 'Género' },
    { titulo: 'Nombre del libro 3', genero: 'Género' },
  ];
}
