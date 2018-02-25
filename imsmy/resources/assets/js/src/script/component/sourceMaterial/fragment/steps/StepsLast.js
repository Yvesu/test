import React, { Component } from 'react'

import ReleasePreview from './ReleasePreview'
import ReleaseCompleted from '../../../common/completed/ReleaseCompleted'
class StepsLast extends Component{
  render(){
    console.log(this.props.completed===true);
    return(
      <div>
        {
          (this.props.completed ===true)?
          <ReleaseCompleted onChange={this.props.onChange}/> : <ReleasePreview nowData={this.props.nowData} />

        }
      </div>
    )
  }
}

export default StepsLast
