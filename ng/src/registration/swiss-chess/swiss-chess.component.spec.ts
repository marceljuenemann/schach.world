import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SwissChessComponent } from './swiss-chess.component';
import { inputBinding, Signal, signal } from '@angular/core';
import { Tournament } from '../tournament';
import { TournamentBuilder } from '../testing/tournament-builder';

describe('SwissChessComponent', () => {
  let component: SwissChessComponent;
  let fixture: ComponentFixture<SwissChessComponent>;
  let tournament: Signal<Tournament>

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [SwissChessComponent]
    })
    .compileComponents();

    tournament = signal(new TournamentBuilder()
      .addPlayers({'A': 3})
      .build());

    fixture = TestBed.createComponent(SwissChessComponent, {
      bindings: [
        inputBinding('tournament', tournament)
      ]
    });
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should export an empty group correctly', () => {
    const exportData = component.generateExport(tournament().groups.get('B')!);
    expect(exportData).toEqual({player: []});
  })

  it('should export players correctly', () => {
    const exportData = component.generateExport(tournament().groups.get('A')!);
    expect(exportData).toEqual({player: [
      { lastname: 'Jünemann, Marcel', teamname: 'SK Lehrte', fed: '', rtg_fid: '1750', rtg_nat: '1600', title: '', birth: '2000', nat_id: '', fide_id: '42943253', player_id: '', sex: 'M', select: '', zps_fed: '', zps_team: '70156', zps_play: '0116', info_play_1: '', info_play_2: '', info_play_3: '', info_play_4: '' },
      { lastname: 'Jünemann, Marcel', teamname: 'SK Lehrte', fed: '', rtg_fid: '1750', rtg_nat: '1600', title: '', birth: '2000', nat_id: '', fide_id: '42943253', player_id: '', sex: 'M', select: '', zps_fed: '', zps_team: '70156', zps_play: '0116', info_play_1: '', info_play_2: '', info_play_3: '', info_play_4: '' },
      { lastname: 'Jünemann, Marcel', teamname: 'SK Lehrte', fed: '', rtg_fid: '1750', rtg_nat: '1600', title: '', birth: '2000', nat_id: '', fide_id: '42943253', player_id: '', sex: 'M', select: '', zps_fed: '', zps_team: '70156', zps_play: '0116', info_play_1: '', info_play_2: '', info_play_3: '', info_play_4: '' },
    ]});
  })
});
