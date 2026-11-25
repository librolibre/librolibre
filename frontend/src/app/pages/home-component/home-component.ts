// import { Component } from '@angular/core';
import { Component, inject } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
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
  // Aviso si la cuenta está suspendida (#24) - Laurence
  protected readonly suspended =
    inject(ActivatedRoute).snapshot.queryParamMap.get('error') === 'suspended';

  protected readonly libros = [
    { titulo: 'Nombre del libro', genero: 'Género' },
    { titulo: 'Nombre del libro 2', genero: 'Género' },
    { titulo: 'Nombre del libro 3', genero: 'Género' },
  ];
}
