import React, { Component } from 'react'
import RaceList from '../common/RaceList'
class RaceSoonEnd extends Component{
  render(){
    return(
      <div className='race_soon_end'>即将截止
        <RaceList uri='/api/test/filmfest' active='3' />
      </div>
    )
  }
}

export default RaceSoonEnd
