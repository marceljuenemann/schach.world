import React from 'react';
import { MapContainer, Marker, Popup, TileLayer } from 'react-leaflet';

// TODO: Determine automatically
const INIT_LAT = 52.8861352
const INIT_LON = 9.1848507
const INIT_ZOOM = 7

/**
 * Displays a map with clubs.
 */
export class ClubsMap extends React.Component<{
  }, {
  }> {

  constructor(props: any) {
    super(props)
    this.state = {
    }
  }

  render() {
    return (
      <MapContainer style={{height: '100%'}} center={[INIT_LAT, INIT_LON]} zoom={INIT_ZOOM} scrollWheelZoom={true}>
        {/* TODO: Attribute schach.in */}
        <TileLayer
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />
        <Marker position={[INIT_LAT, INIT_LON]}>
          <Popup>
            A pretty CSS3 popup. <br /> Easily customizable.
          </Popup>
        </Marker>
      </MapContainer>
    );
  }
}
