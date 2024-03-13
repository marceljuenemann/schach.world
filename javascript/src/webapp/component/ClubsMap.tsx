import L, { Icon, LatLngTuple } from 'leaflet';
import React from 'react';
import { MapContainer, Marker, Popup, TileLayer } from 'react-leaflet';

export interface District {
  name: string
  website: string
  markerColor: string
  clubs: Club[]
}

export interface Club {
  zps: string
  name: string
  website: string | null
  instagramUri: string | null
  detailsUri: string
  dwzUri: string
  venue: {
    latitude: string
    longitude: string
  }
  stats: {
    members: number
    members_u25: number
    members_female: number
    avg_age: number
    avg_rating: number
  }
}

/**
 * Displays a map with clubs.
 */
export class ClubsMap extends React.Component<{
    data: {
      districts: District[],
      lat: number,
      lon: number,
      zoom: number
    }
  }> {

  constructor(props: any) {
    super(props)
  }

  render() {
    L.Icon.Default.imagePath = '/core/nsv2020/images/marker/';
    const coordinates = [this.props.data.lat, this.props.data.lon] as LatLngTuple
    const showDistrict = this.props.data.districts.length > 1
    return (
      <MapContainer style={{height: '100%'}} center={coordinates} zoom={this.props.data.zoom} scrollWheelZoom={true}>
        <TileLayer
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors | <a href="https://schach.in">schach.in</a>'
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />
        {
          Object.values(this.props.data.districts).map(district => {
            const icon = new L.Icon.Default({
              iconUrl: `marker-icon-${district.markerColor}.png`,
              iconRetinaUrl: `marker-icon-${district.markerColor}-2x.png`
            }) as Icon;
            return Object.values(district.clubs).map(club => this.renderClub(club, district, showDistrict, icon))
          }).flat()
        }
      </MapContainer>
    );
  }

  private renderClub(club: Club, district: District, showDistrict: boolean, icon: Icon) {
    const coordinates = [parseFloat(club.venue!.latitude), parseFloat(club.venue!.longitude)] as [number, number]
    return (
      <Marker key={club.zps} position={coordinates} icon={icon}>
        <Popup>
          <h6>{club.name}</h6>
          {club.website && <><a href={club.website}>{this.prettyWebsite(club.website)}</a><br /></>}

          {club.instagramUri && <><a href={club.instagramUri}>Instagram</a>&nbsp;|&nbsp;</>}
          <a href={club.detailsUri}>Details</a>&nbsp;|&nbsp;
          <a href={club.dwzUri}>DWZ-Liste</a><br />

          {club.stats.members} Mitglieder ({club.stats.members_u25} U25)<br />
          ø-DWZ: {club.stats.avg_rating}&nbsp;&nbsp;ø-Alter: {club.stats.avg_age}<br />
          {showDistrict && <a href={district.website}>{district.name}</a>}
        </Popup>
      </Marker>
    )
  }

  private prettyWebsite(url: string) {
    url = url.replace('https://', '')
    url = url.replace('http://', '')
    url = url.replace('www.', '')
    return url
  } 

}
