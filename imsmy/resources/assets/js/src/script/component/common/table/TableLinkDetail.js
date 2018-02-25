import React, { Component } from 'react'
import { Link } from 'react-router'
import { Icon } from 'antd'

class TableLinkDetail extends Component{
  render(){
    return(
      <Link to={this.props.linkUri? this.props.linkUri : null}>
        <div className={this.props.noMask? 'cover_box_noneed_mask' : 'cover_box'}>
          <img className="detail_img_hover"
            src={this.props.imgUri}/>
            <div className="detail_img_mask">
              <Icon type="play-circle"  style={{fontSize:"18px",color:"#fff"}}/>
            </div>
        </div>
      </Link>
    )
  }
}

export default TableLinkDetail
