import { Component, OnInit, inject, signal } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Footer } from '../../components/footer/footer';
import { HeaderComponent } from '../../components/header/header';
import { LibroCard } from '../../components/libro-card/libro-card';
import { CatalogService } from '../../core/catalog.service';
import { CatalogBook } from '../../core/models';
import { Libro } from '../../interfaces/libro';

@Component({
  imports: [HeaderComponent, LibroCard, Footer],
  selector: 'app-home-component',
  styleUrl: './home-component.css',
  templateUrl: './home-component.html',
})
export class HomeComponent implements OnInit {
  private readonly catalogService = inject(CatalogService);

  protected readonly suspended =
    inject(ActivatedRoute).snapshot.queryParamMap.get('error') === 'suspended';

  protected readonly libros = signal<Libro[]>([]);
  protected readonly cargando = signal(true);

  ngOnInit(): void {
    this.catalogService.getBooks({ per_page: 6 }).subscribe({
      next: (res) => {
        const mapeados: Libro[] = res.data.map((book: CatalogBook) => ({
          id: book.id,
          titulo: book.title,
          genero: book.category?.name ?? 'General',
          portada: book.cover_url,
        }));
        this.libros.set(mapeados);
        this.cargando.set(false);
      },
      error: () => {
        this.cargando.set(false);
      },
    });
  }
}
