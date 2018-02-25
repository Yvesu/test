import React, { Component } from 'react'
import RaceList from '../common/RaceList'

class RaceNewStart extends Component{
  render(){
    return(
      <div className='race_new_start'>最新发起
        <RaceList uri='/api/test/filmfest' active='2' />
      </div>
    )
  }
}

export default RaceNewStart
