import React, { Component } from 'react'

import LibraryPreview from './LibraryPreview'
import ReleaseCompleted from '../../../../common/completed/ReleaseCompleted'
class UploadLast extends Component{
  render(){

    return(
      <div>
        {
          (this.props.completed ===true)?
          <ReleaseCompleted onChange={this.props.onChange}/> : <LibraryPreview nowData={this.props.nowData} />

        }
      </div>
    )
  }
}

export default UploadLast
