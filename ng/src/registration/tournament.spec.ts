import { TEST_PLAYER, TournamentBuilder } from "./testing/tournament-builder";
import { Tournament } from "./tournament";

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

  describe('hasPlayer', () => {
    let tournament: Tournament

    beforeEach(() => {
      tournament = new TournamentBuilder()
        .addPlayer({group: 'A'})
        .build();
    });

    it('should return true if player with same ZPS and member Id exists', () => {
      expect(tournament.hasPlayer({name: 'Doe', zps: TEST_PLAYER.playerData.zps, memberId: TEST_PLAYER.playerData.memberId})).toBe(true);
    });

    it('should return true if no ZPS and player with same name exists', () => {
      expect(tournament.hasPlayer({name: TEST_PLAYER.playerData.name, zps: '', memberId: ''})).toBe(true);
    });

    it('should return false if player with same name but different ZPS exists', () => {
      expect(tournament.hasPlayer({name: TEST_PLAYER.playerData.name, zps: '70101', memberId: '1234'})).toBe(false);
    });

    it('should return false if player is not in tournament', () => {
      expect(tournament.hasPlayer({name: 'Doe', zps: TEST_PLAYER.playerData.zps, memberId: '67890'})).toBe(false);
    });
  })
})

describe('Tournament', () => {
  describe('deadlinePassed', () => {
    it('should return false for a future deadline', () => {
      const tournament = new TournamentBuilder().config({deadline: '2099-12-31'}).build()
      expect(tournament.deadlinePassed).toBe(false)
    })

    it('should return true for a past deadline', () => {
      const tournament = new TournamentBuilder().config({deadline: '2000-01-01'}).build()
      expect(tournament.deadlinePassed).toBe(true)
    })
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
