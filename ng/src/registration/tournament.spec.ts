import { TournamentBuilder } from "./testing/tournament-builder";

describe('Tournament', () => {
  describe('availableSlots', () => {
    it('should return maxPlayers with no players', () => {
      const tournament = new TournamentBuilder().config({maxPlayers: 5}).build();
      expect(tournament.availableSlots).toEqual(5);
    })

    it('should return available slots with existing players', () => {
      const tournament = new TournamentBuilder()
        .config({maxPlayers: 5})
        .addPlayers({'A': 3, 'C': 1})
        .build();
      expect(tournament.availableSlots).toEqual(1);
    });

    it('should return 0 with no slots left', () => {
      const tournament = new TournamentBuilder()
        .config({maxPlayers: 5})
        .addPlayers({'A': 3, 'C': 10})
        .build();
      expect(tournament.availableSlots).toEqual(0);
    });

    it('should return Infinity if no limit set', () => {
      const tournament = new TournamentBuilder()
        .config({maxPlayers: null})
        .addPlayers({'A': 3, 'C': 10})
        .build();
      expect(tournament.availableSlots).toEqual(Infinity);
    });
  })
})

describe('Group', () => {
  describe('availableSlots', () => {
    it('should return maxPlayers with no players', () => {
      const tournament = new TournamentBuilder().groupConfig('A', {maxPlayers: 5}).build();
      expect(tournament.groups.get('A')?.availableSlots).toEqual(5);
    })

    it('should return available slots with existing players', () => {
      const tournament = new TournamentBuilder()
        .groupConfig('A', {maxPlayers: 5})
        .addPlayers({'A': 3, 'C': 1})
        .build();
      expect(tournament.groups.get('A')?.availableSlots).toEqual(2);
    });

    it('should return 0 with no slots left', () => {
      const tournament = new TournamentBuilder()
        .groupConfig('A', {maxPlayers: 5})
        .addPlayers({'A': 5})
        .build();
      expect(tournament.groups.get('A')?.availableSlots).toEqual(0);
    });

    it('should return tournament limit if no group limit set', () => {
      const tournament = new TournamentBuilder()
        .config({maxPlayers: 10})
        .groupConfig('A', {maxPlayers: null})
        .addPlayers({'A': 5})
        .build();
      expect(tournament.groups.get('A')?.availableSlots).toEqual(5);
    });

    it('should return tournament limit if lower than group limit', () => {
      const tournament = new TournamentBuilder()
        .config({maxPlayers: 10})
        .groupConfig('A', {maxPlayers: 5})
        .addPlayers({'A': 2, 'C': 7})
        .build();
      expect(tournament.groups.get('A')?.availableSlots).toEqual(1);
    });

    it('should return Infinity if no limit set', () => {
      const tournament = new TournamentBuilder()
        .config({maxPlayers: null})
        .groupConfig('A', {maxPlayers: null})
        .addPlayers({'A': 3, 'C': 10})
        .build();
      expect(tournament.groups.get('A')?.availableSlots).toEqual(Infinity);
    });

    it('should return constraint if no other limit set', () => {
      const tournament = new TournamentBuilder()
        .config({maxPlayers: null, constraints: [{groups: ['A', 'B'], maxPlayers: 10}]})
        .groupConfig('A', {maxPlayers: null})
        .groupConfig('B', {maxPlayers: null})
        .groupConfig('C', {maxPlayers: null})
        .addPlayers({'A': 3, 'B': 2, 'C': 1})
        .build();
      expect(tournament.groups.get('A')?.availableSlots).toEqual(5);
      expect(tournament.groups.get('B')?.availableSlots).toEqual(5);
      expect(tournament.groups.get('C')?.availableSlots).toEqual(Infinity);
    });

    it('should return lowest applicable limit', () => {
      const tournament = new TournamentBuilder()
        .config({maxPlayers: 10, constraints: [{groups: ['A', 'B'], maxPlayers: 5}]})
        .groupConfig('A', {maxPlayers: 3})
        .groupConfig('B', {maxPlayers: 7})
        .groupConfig('C', {maxPlayers: 15})
        .build();
      expect(tournament.groups.get('A')?.availableSlots).toEqual(3);
      expect(tournament.groups.get('B')?.availableSlots).toEqual(5);
      expect(tournament.groups.get('C')?.availableSlots).toEqual(10);
    });
  })
})
