import { Component, input } from '@angular/core';
import { Group, Tournament } from '../tournament';
import { downloadJson } from '../../core/util';

@Component({
  selector: 'swiss-chess-export',
  imports: [],
  templateUrl: './swiss-chess.component.html',
  styleUrl: './swiss-chess.component.css'
})
export class SwissChessComponent {
  tournament = input.required<Tournament>()

  export(group: Group) {
    downloadJson(this.generateExport(group), `${group.id}.json`);
  }

  // visible-for-testing
  generateExport(group: Group) {
    const players = group.players.map(player => {
      const data = player.playerData;
      return {
        "lastname": data.name,
        "teamname": data.club || '',
        "fed": "",
        "rtg_fid": (data.elo || '').toString(),
        "rtg_nat": (data.dwz || '').toString(),
        "title": data.fideTitle || '',
        "birth": (data.yearOfBirth || '').toString(),
        "nat_id": "",
        "fide_id": (data.fideId || '').toString(),
        "player_id": "",
        "sex": data.gender || '',
        "select": "",
        "zps_fed": "",
        "zps_team": data.zps || '',
        "zps_play": data.memberId || '',
        "info_play_1": "",
        "info_play_2": "",
        "info_play_3": "",
        "info_play_4": "",
      }
    })
    return { player: players }
  }
}
