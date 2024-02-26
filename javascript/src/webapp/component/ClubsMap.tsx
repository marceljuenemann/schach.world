import L, { Icon, LatLngTuple } from 'leaflet';
import React from 'react';
import { MapContainer, Marker, Popup, TileLayer } from 'react-leaflet';

// TODO: Determine automatically
const INIT_LAT = 52.77
const INIT_LON = 9.07
const INIT_ZOOM = 7

export interface District {
  name: string
  markerColor: string
  clubs: Club[]
}

export interface Club {
  zps: string
  name: string
  dwzUri: string
  properties: {
    coordinates: LatLngTuple
    awards: number
    members: number
    u25: number
    female: number
    avg_age: number
    avg_rating: number
  }
}

/**
 * Displays a map with clubs.
 */
export class ClubsMap extends React.Component<{
    districts: District[]
  }, {
  }> {

  constructor(props: any) {
    super(props)
  }

  render() {
    L.Icon.Default.imagePath = '/core/nsv2020/images/marker/';
    return (
      <MapContainer style={{height: '100%'}} center={[INIT_LAT, INIT_LON]} zoom={INIT_ZOOM} scrollWheelZoom={true}>
        {/* TODO: Attribute schach.in */}
        <TileLayer
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />
        {
          Object.values(this.props.districts).map(district => {
            const icon = new L.Icon.Default({
              iconUrl: `marker-icon-${district.markerColor}.png`,
              iconRetinaUrl: `marker-icon-red-${district.markerColor}.png`
            }) as Icon;
            return Object.values(district.clubs).map(club => this.renderClub(club, icon))
          }).flat()
        }
      </MapContainer>
    );
  }

  // TODO: Links
  // TODO: Bezirk
  renderClub(club: Club, icon: Icon) {
    return (
      <Marker key={club.zps} position={club.properties.coordinates} icon={icon}>
        <Popup>
          <h6>{club.name}</h6>

          {club.properties.members} Mitglieder ({club.properties.u25} U25)<br />
          ø-DWZ: {club.properties.avg_rating}&nbsp;&nbsp;&nbsp;ø-Alter: {club.properties.avg_age}
        </Popup>
      </Marker>
    )
  }

}
