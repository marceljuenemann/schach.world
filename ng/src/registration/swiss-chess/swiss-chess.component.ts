import { Component, input } from '@angular/core';
import { Tournament } from '../tournament';

@Component({
  selector: 'swiss-chess-export',
  imports: [],
  templateUrl: './swiss-chess.component.html',
  styleUrl: './swiss-chess.component.css'
})
export class SwissChessComponent {
  tournament = input.required<Tournament>()

  download() {

  }
}
