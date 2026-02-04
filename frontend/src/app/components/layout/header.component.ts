import { Component } from '@angular/core';
import { RouterLink, RouterLinkActive } from '@angular/router';

@Component({
  selector: 'app-header',
  standalone: true,
  imports: [RouterLink, RouterLinkActive],
  template: `
    <header class="header">
      <div class="header-container">
        <a routerLink="/" class="logo">
          <span class="logo-icon">📋</span>
          <span class="logo-text">PLACE Licitaciones</span>
        </a>

        <nav class="nav">
          <a routerLink="/dashboard" routerLinkActive="active" class="nav-link">
            Dashboard
          </a>
          <a routerLink="/licitaciones" routerLinkActive="active" class="nav-link">
            Licitaciones
          </a>
          <a routerLink="/alertas" routerLinkActive="active" class="nav-link">
            Mis Alertas
          </a>
        </nav>
      </div>
    </header>
  `,
  styles: [`
    .header {
      background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
      color: white;
      padding: 0 2rem;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .header-container {
      max-width: 1400px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
      height: 64px;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      text-decoration: none;
      color: white;
      font-weight: 600;
      font-size: 1.25rem;
    }

    .logo-icon {
      font-size: 1.5rem;
    }

    .nav {
      display: flex;
      gap: 0.5rem;
    }

    .nav-link {
      color: rgba(255,255,255,0.85);
      text-decoration: none;
      padding: 0.5rem 1rem;
      border-radius: 6px;
      transition: all 0.2s;
      font-weight: 500;

      &:hover {
        background: rgba(255,255,255,0.1);
        color: white;
      }

      &.active {
        background: rgba(255,255,255,0.2);
        color: white;
      }
    }
  `]
})
export class HeaderComponent {}
