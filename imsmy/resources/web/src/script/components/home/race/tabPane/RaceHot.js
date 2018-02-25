import React, { Component } from 'react'
import RaceList from '../common/RaceList'
class RaceHot extends Component{
  render(){
    return(
      <div className="race_hot">
        <h3>为您列出 <b>123</b> 场竞赛</h3>
        <RaceList uri='/api/test/filmfest' active='1'/>
      </div>
    )
  }
}

export default RaceHot
